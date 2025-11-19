<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetCode;
use App\Models\User;
use Carbon\Carbon;
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

        try {
            Mail::to($email)->send(new PasswordResetCode($code));
        } catch (\Exception $e) {
            report($e);
            Log::info("Password reset code for {$email}: {$code}");

            if ($this->shouldShowDebugCode() || app()->environment('local')) {
                return redirect()->route('password.enter_code')
                    ->with('email', $email)
                    ->with('status', "Verification code logged for {$email}. Check storage/logs/laravel.log or your mail log.")
                    ->with('debug_code', $code)
                    ->with('show_debug_code', true);
            }

            return back()->withErrors(['email' => 'Failed to send reset code. Please configure mail or try again later.']);
        }

        if ($request->wantsJson() || $request->ajax()) {
            $data = ['status' => 'code_sent'];
            if ($this->shouldShowDebugCode()) {
                $data['debug_code'] = $code;
                $data['show_debug_code'] = true;
            }

            return response()->json($data, 200);
        }

        $redirect = redirect()->route('password.enter_code')
            ->with('email', $email)
            ->with('status', 'A verification code has been sent to your email.');

        if ($this->shouldShowDebugCode()) {
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
}
