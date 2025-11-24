<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetCode;
use App\Models\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordController extends Controller
{
    protected const CODE_TTL_MINUTES = 3;

    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $email = $request->input('email');
        $code = $this->storeVerificationCode($email);

        $emailSent = false;
        $errorMessage = null;

        try {
            // Log mail configuration before sending
            Log::info("Attempting to send email via " . config('mail.default'));
            Log::info("SMTP Host: " . config('mail.mailers.smtp.host'));
            Log::info("SMTP Port: " . config('mail.mailers.smtp.port'));
            Log::info("SMTP Username: " . config('mail.mailers.smtp.username'));
            Log::info("From Address: " . config('mail.from.address'));

            // Try to send email synchronously with timeout
            $emailSent = $this->sendViaSendGridApi($email, $code);
            if ($emailSent) {
                Log::info("Password reset email sent via SendGrid API to {$email}");
            } else {
                throw new \Exception("SendGrid API send failed");
            }
        } catch (\Exception $e) {
            report($e);
            $errorMessage = $e->getMessage();
            Log::error("Failed to send password reset email to {$email}: " . $errorMessage);
            Log::error("Exception class: " . get_class($e));
            Log::error("Exception trace: " . $e->getTraceAsString());
            Log::info("Password reset code for {$email}: {$code}");
        }

        // Always allow user to proceed (show code in development/local only when email fails)
        $showDebug = ($this->shouldShowDebugCode() && !$emailSent) || (app()->environment('local') && !$emailSent);

        if ($request->wantsJson() || $request->ajax()) {
            $data = ['status' => $emailSent ? 'code_sent' : 'code_logged'];
            if ($showDebug) {
                $data['debug_code'] = $code;
                $data['show_debug_code'] = true;
                if (!$emailSent) {
                    $data['email_error'] = 'Email sending failed. Use the code shown below.';
                }
            }
            return response()->json($data, 200);
        }

        $statusMessage = $emailSent
            ? 'A verification code has been sent to your email.'
            : "Unable to send email. Your verification code is: {$code}";

        $redirect = redirect()->route('password.enter_code')
            ->with('email', $email)
            ->with('status', $statusMessage);

        if ($showDebug) {
            $redirect = $redirect->with('debug_code', $code)->with('show_debug_code', true);
        }

        return $redirect;
    }

    public function resendCode(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $email = $request->input('email');
        $code = $this->storeVerificationCode($email);

        try {
            Mail::to($email)->send(new PasswordResetCode($code));
        } catch (\Exception $e) {
            report($e);
            Log::info("Password reset code for {$email}: {$code}");

            $payload = ['error' => 'Failed to send reset code. Please try again later.'];
            if ($this->shouldShowDebugCode()) {
                $payload['debug_code'] = $code;
                $payload['status'] = "Verification code logged for {$email}. Check storage/logs/laravel.log.";
                $payload['show_debug_code'] = true;
            }

            return response()->json($payload, 500);
        }

        $payload = ['status' => 'Verification code resent to your email.'];
        if ($this->shouldShowDebugCode()) {
            $payload['debug_code'] = $code;
            $payload['show_debug_code'] = true;
        }

        return response()->json($payload, 200);
    }

    public function showEnterCodeForm(Request $request)
    {
        $email = session('email') ?? $request->query('email') ?? '';
        return view('auth.passwords.enter_code', ['email' => $email]);
    }

    public function showSetNewForm(Request $request)
    {
        $token = $request->query('token') ?? '';
        $email = $request->query('email') ?? '';
        return view('auth.passwords.set_new', ['token' => $token, 'email' => $email]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|confirmed|min:6',
        ]);

        $email = $request->input('email');
        $record = DB::table('password_resets')->where('email', $email)->first();
        if (!$record) {
            return back()->withInput()->withErrors(['email' => 'No reset request found for that email.']);
        }

        $created = Carbon::parse($record->created_at);
        if ($created->diffInMinutes(now()) > self::CODE_TTL_MINUTES) {
            return back()->withInput()->withErrors(['token' => "The verification code has expired (codes last for {self::CODE_TTL_MINUTES} minutes). Please request a new one."]);
        }

        if (!Hash::check($request->input('token'), $record->token)) {
            return back()->withInput()->withErrors(['token' => 'Invalid verification code.']);
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            return back()->withInput()->withErrors(['email' => 'No user found with that email.']);
        }

        $user->password = Hash::make($request->input('password'));
        $user->save();

        DB::table('password_resets')->where('email', $email)->delete();

        return redirect()->route('login')
            ->with('success', 'Your password has been updated. You can now sign in.')
            ->with('status', 'Your password has been updated. You can now sign in.');
    }

    public function checkCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string'
        ]);

        $record = DB::table('password_resets')->where('email', $request->input('email'))->first();
        if (!$record) {
            return back()->withErrors(['email' => 'No reset request found for that email.']);
        }

        $created = Carbon::parse($record->created_at);
        if ($created->diffInMinutes(now()) > self::CODE_TTL_MINUTES) {
            return back()->withErrors(['code' => "The verification code has expired (codes last for {self::CODE_TTL_MINUTES} minutes). Please request a new one."]);
        }

        if (Hash::check($request->input('code'), $record->token)) {
            return redirect()->route('password.set_new', ['token' => $request->input('code'), 'email' => $record->email]);
        }

        return back()->withErrors(['code' => 'Invalid verification code.']);
    }

    protected function storeVerificationCode(string $email): string
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        DB::table('password_resets')->updateOrInsert(
            ['email' => $email],
            ['token' => Hash::make($code), 'created_at' => Carbon::now()]
        );

        return $code;
    }

    protected function shouldShowDebugCode(): bool
    {
        return config('mail.default') === 'log' || env('MAIL_MAILER') === 'log';
    }

    protected function sendViaSendGridApi(string $email, string $code): bool
    {
        try {
            $apiKey = config('mail.mailers.smtp.password');
            $fromEmail = config('mail.from.address');
            $fromName = config('mail.from.name');

            $client = new Client(['timeout' => 30]);

            $response = $client->post('https://api.sendgrid.com/v3/mail/send', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'personalizations' => [
                        [
                            'to' => [['email' => $email]],
                        ],
                    ],
                    'from' => [
                        'email' => $fromEmail,
                        'name' => $fromName,
                    ],
                    'subject' => 'Your password reset code',
                    'content' => [
                        [
                            'type' => 'text/html',
                            'value' => view('emails.password_reset_code', ['code' => $code])->render(),
                        ],
                    ],
                ],
            ]);

            return $response->getStatusCode() === 202;
        } catch (\Exception $e) {
            Log::error("SendGrid API error: " . $e->getMessage());
            return false;
        }
    }
}
