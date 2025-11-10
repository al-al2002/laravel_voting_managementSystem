<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\PasswordResetCode;
use Carbon\Carbon;

class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    /**
     * Send a numeric reset code to the user's email and store it in password_resets.
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $email = $request->input('email');

        // generate a 6-digit numeric code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // store (or update) the code in password_resets table
        DB::table('password_resets')->updateOrInsert(
            ['email' => $email],
            ['token' => $code, 'created_at' => Carbon::now()]
        );

        // send the code via email using a simple Mailable
        try {
            Mail::to($email)->send(new PasswordResetCode($code));
        } catch (\Exception $e) {
            // If sending fails (e.g., mail not configured), log the code so developer can find it.
            report($e);
            Log::info("Password reset code for {$email}: {$code}");

            // For local development, show the code in session so tester can quickly use it.
            if (app()->environment('local') || config('mail.default') === 'log') {
                return redirect()->route('password.enter_code')
                    ->with('email', $email)
                    ->with('status', "Verification code logged for {$email}. Check storage/logs/laravel.log or your mail log.")
                    ->with('debug_code', $code);
            }

            return back()->withErrors(['email' => 'Failed to send reset code. Please configure mail or try again later.']);
        }

        if ($request->wantsJson() || $request->ajax()) {
            $data = ['status' => 'code_sent'];
            if (app()->environment('local') || config('mail.default') === 'log' || env('MAIL_MAILER') === 'log') {
                $data['debug_code'] = $code;
            }
            return response()->json($data, 200);
        }

        // Redirect user to enter-code page with email prefilled
        $redirect = redirect()->route('password.enter_code')
            ->with('email', $email)
            ->with('status', 'A verification code has been sent to your email.');

        // For local development or when mailer is 'log', also place the numeric code in session
        // so testers can see it on the enter-code page without email delivery.
        if (app()->environment('local') || config('mail.default') === 'log' || env('MAIL_MAILER') === 'log') {
            $redirect = $redirect->with('debug_code', $code);
        }

        return $redirect;
    }

    // Show form where user enters the code they received
    public function showEnterCodeForm(Request $request)
    {
        $email = session('email') ?? $request->query('email') ?? '';
        return view('auth.passwords.enter_code', ['email' => $email]);
    }

    // Show a simple set-new-password form (after code verification)
    public function showSetNewForm(Request $request)
    {
        $token = $request->query('token') ?? '';
        $email = $request->query('email') ?? '';
        return view('auth.passwords.set_new', ['token' => $token, 'email' => $email]);
    }

    // Verify the code and redirect to reset page if valid
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

        // check expiration (default 60 minutes)
        $created = Carbon::parse($record->created_at);
        if ($created->diffInMinutes(now()) > 60) {
            return back()->withErrors(['code' => 'The verification code has expired. Please request a new one.']);
        }

        if (hash_equals($record->token, $request->input('code'))) {
            // valid -> redirect to set-new-password form (token is the code)
            return redirect()->route('password.set_new', ['token' => $record->token, 'email' => $record->email]);
        }

        return back()->withErrors(['code' => 'Invalid verification code.']);
    }
}
