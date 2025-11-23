<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    /**
     * Show the registration form.
     */
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    /**
     * Handle registration request.
     */
  public function register(Request $request)
{
    // Validate input
    $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
        'voter_id' => ['required', 'digits:6', 'unique:users'],
        'password' => ['required', 'string', 'min:6', 'confirmed'],
        'agree' => ['accepted'],
    ], [
        'voter_id.digits' => 'Voter ID must be exactly 6 digits.',
        'password.confirmed' => 'Password and Confirm Password do not match.',
        'agree.accepted' => 'You must agree to the terms and conditions.',
    ]);

    // Check if email exists in admins table
    if (Admin::where('email', $request->email)->exists()) {
        return back()->withErrors([
            'email' => 'This email is already registered.',
        ])->withInput();
    }

    // Create voter user
    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'voter_id' => $request->voter_id,
        'password' => Hash::make($request->password),
    ]);

    // Log in the user automatically
    Auth::login($user);

    // Redirect to user dashboard
    return redirect()->route('user.dashboard');
}

}
