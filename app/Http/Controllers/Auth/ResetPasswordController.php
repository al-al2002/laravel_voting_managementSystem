<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;

class ResetPasswordController extends Controller
{
    /**
     * Show the password reset form
     */
    public function showResetForm(Request $request, $token = null)
    {
        // Ensure token and email are passed
        return view('auth.passwords.reset', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    /**
     * Handle the password reset
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:6',
        ]);

        // Attempt to reset the password
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->setRememberToken(Str::random(60));
                $user->save();
                event(new PasswordReset($user));
            }
        );

        // Handle AJAX requests
        if ($request->wantsJson() || $request->ajax()) {
            return $status === Password::PASSWORD_RESET
                ? response()->json(['status' => __($status)], 200)
                : response()->json(['email' => __($status)], 422);
        }

        // Handle normal form submission
        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', __('Your password has been reset!'))
            : back()->withErrors(['email' => [__($status)]]);
    }
}
