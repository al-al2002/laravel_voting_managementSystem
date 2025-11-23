<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $loginInput = $request->input('login');

        // Check if input is email or voter_id
        $isEmail = filter_var($loginInput, FILTER_VALIDATE_EMAIL);
        $isVoterId = !$isEmail && is_numeric($loginInput) && strlen($loginInput) === 6;

        // Validate login fields
        $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required'],
        ]);

        // Additional validation for voter ID format if it's numeric
        if (!$isEmail && is_numeric($loginInput) && !$isVoterId) {
            return back()->withErrors([
                'login' => 'Voter ID must be exactly 6 digits.',
            ])->withInput($request->only('login'));
        }

        // Try to authenticate as admin first (email only)
        if ($isEmail) {
            $admin = Admin::where('email', $loginInput)->first();

            if ($admin && Hash::check($request->password, $admin->password)) {
                // Regenerate session first to get new session ID
                $request->session()->regenerate();

                // Then set admin session data
                $request->session()->put('auth_type', 'admin');
                $request->session()->put('auth_id', $admin->id);

                // Force immediate save
                $request->session()->save();

                return redirect('/admin/dashboard');
            }
        }        // Try to authenticate as user (email or voter_id)
        if ($isEmail || $isVoterId) {
            $field = $isEmail ? 'email' : 'voter_id';
            $user = User::where($field, $loginInput)->first();

            if ($user && Hash::check($request->password, $user->password)) {
                Auth::login($user, $request->filled('remember'));
                $request->session()->regenerate();
                return redirect()->route('user.dashboard');
            }
        }

        return back()->withErrors([
            'login' => 'Invalid credentials.',
        ])->withInput($request->only('login'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
