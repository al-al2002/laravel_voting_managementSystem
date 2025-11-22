<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class ProfileController extends Controller
{
    /**
     * Show the profile edit form.
     */
    public function edit()
    {
        return view('user.profile.edit', [
            'user' => Auth::user(),
        ]);
    }

    /**
     * Update the user's profile.
     */
    public function update(Request $request)
    {
        // ✅ Always fetch as Eloquent model
        $user = User::find(Auth::id());

        if (!$user) {
            return redirect()->back()->with('error', 'User not found.');
        }

        // ✅ Validation
        $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|max:255|unique:users,email,' . $user->id,
            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // ✅ Assign values
        $user->name  = $request->name;
        $user->email = $request->email;

        // ✅ Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            // Delete old photo if it exists
            if ($user->profile_photo) {
                try {
                    Storage::disk('supabase')->delete($user->profile_photo);
                } catch (\Exception $e) {
                    Log::warning('Failed to delete old photo: ' . $e->getMessage());
                }
            }

            // Store new photo
            try {
                $path = $request->file('profile_photo')->store('profile-photos', 'supabase');
                $user->profile_photo = $path;
                Log::info('Profile photo uploaded to Supabase: ' . $path);
            } catch (\Exception $e) {
                Log::error('Failed to upload profile photo: ' . $e->getMessage());
                return redirect()->back()->with('error', 'Failed to upload photo: ' . $e->getMessage());
            }
        }

        $user->save();

        return redirect()->back()->with('success', 'Profile updated successfully!');
    }
}
