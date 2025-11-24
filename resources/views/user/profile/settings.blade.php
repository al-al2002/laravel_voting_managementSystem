@extends('layouts.user')

@section('title', 'Profile Settings')

@section('content')
    <div class="max-w-xl mx-auto bg-[#10243F] p-6 rounded-lg shadow border border-gray-700 text-white">
        {{-- Back Arrow --}}
        <a href="{{ route('user.dashboard') }}" class="flex items-center text-gray-300 mb-4 hover:text-white">
            ← Back
        </a>

        <h2 class="text-xl font-bold mb-6">Profile Settings</h2>

        {{-- Change Password Section --}}
        <div class="bg-[#1a2f4a] p-5 rounded-lg border border-gray-600">
            <h3 class="text-lg font-semibold mb-4">Change Password</h3>

            {{-- Success Message --}}
            @if (session('success'))
                <div class="mb-4 p-3 bg-green-600 text-white rounded">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Error Messages --}}
            @if ($errors->any())
                <div class="mb-4 p-3 bg-red-600 text-white rounded">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Change Password Form --}}
            <form method="POST" action="{{ route('user.password.update') }}" id="changePasswordForm">
                @csrf

                {{-- Current Password --}}
                <div class="mb-4 relative">
                    <label for="current_password" class="block font-medium mb-1">Current Password</label>
                    <input type="password" name="current_password" id="current_password"
                        class="w-full border border-gray-600 bg-gray-800 text-white px-3 py-2 rounded pr-10" required>
                    <button type="button" onclick="togglePassword('current_password', this)"
                        class="absolute right-3 top-9 text-gray-400 hover:text-white">👁</button>
                </div>

                {{-- New Password --}}
                <div class="mb-4 relative">
                    <label for="new_password" class="block font-medium mb-1">New Password</label>
                    <input type="password" name="new_password" id="new_password"
                        class="w-full border border-gray-600 bg-gray-800 text-white px-3 py-2 rounded pr-10" required>
                    <button type="button" onclick="togglePassword('new_password', this)"
                        class="absolute right-3 top-9 text-gray-400 hover:text-white">👁</button>
                </div>

                {{-- Confirm New Password --}}
                <div class="mb-4 relative">
                    <label for="new_password_confirmation" class="block font-medium mb-1">Confirm New Password</label>
                    <input type="password" name="new_password_confirmation" id="new_password_confirmation"
                        class="w-full border border-gray-600 bg-gray-800 text-white px-3 py-2 rounded pr-10" required>
                    <button type="button" onclick="togglePassword('new_password_confirmation', this)"
                        class="absolute right-3 top-9 text-gray-400 hover:text-white">👁</button>
                </div>

                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded"
                    id="updatePasswordBtn">
                    <span id="updatePasswordText">Update Password</span>
                    <span id="updatePasswordSpinner" class="hidden">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        Updating...
                    </span>
                </button>
            </form>
        </div>
    </div>

    {{-- Toggle password visibility --}}
    <script>
        function togglePassword(fieldId, btn) {
            const input = document.getElementById(fieldId);
            if (input.type === "password") {
                input.type = "text";
                btn.textContent = "🙈";
            } else {
                input.type = "password";
                btn.textContent = "👁";
            }
        }

        // Loading state for change password form
        document.getElementById('changePasswordForm').addEventListener('submit', function() {
            const btn = document.getElementById('updatePasswordBtn');
            const btnText = document.getElementById('updatePasswordText');
            const btnSpinner = document.getElementById('updatePasswordSpinner');
            btn.disabled = true;
            btn.classList.add('opacity-50', 'cursor-not-allowed');
            btnText.classList.add('hidden');
            btnSpinner.classList.remove('hidden');
        });
    </script>
@endsection
