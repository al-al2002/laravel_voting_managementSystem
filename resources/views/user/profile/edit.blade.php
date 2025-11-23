@extends('layouts.user')

@section('title', 'Edit Profile')

@section('content')
    <div class="max-w-xl mx-auto bg-[#10243F] p-6 rounded-lg shadow border border-gray-700 text-white">
        {{-- Back Arrow --}}
        <a href="{{ route('user.dashboard') }}" class="flex items-center text-gray-300 mb-4 hover:text-white">
            ← Back
        </a>

        <h2 class="text-xl font-bold mb-4">Edit Profile</h2>

        @if (session('success'))
            <div class="bg-green-600 text-white px-4 py-2 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-600 text-white px-4 py-2 rounded mb-4">
                <p class="font-semibold">Please fix the following errors:</p>
                <ul class="list-disc list-inside mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('user.profile.update') }}" method="POST" enctype="multipart/form-data" id="profileForm">
            @csrf

            <div class="mb-4">
                <label class="block font-medium">Name</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}"
                    class="w-full border border-gray-600 bg-gray-800 text-white px-3 py-2 rounded">
                @error('name')
                    <p class="text-red-400 text-sm">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block font-medium">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}"
                    class="w-full border border-gray-600 bg-gray-800 text-white px-3 py-2 rounded">
                @error('email')
                    <p class="text-red-400 text-sm">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block font-medium">Profile Photo</label>
                <input type="file" name="profile_photo"
                    class="w-full border border-gray-600 bg-gray-800 text-white px-3 py-2 rounded">
                @if ($user->profile_photo)
                    <img src="{{ $user->profile_photo_url ?? asset('images/default-avatar.png') }}"
                        class="w-16 h-16 rounded-full mt-2 border border-gray-600">
                @endif
                @error('profile_photo')
                    <p class="text-red-400 text-sm">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded" id="saveBtn">
                <span id="saveBtnText">Save Changes</span>
                <span id="saveBtnSpinner" class="hidden">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    Saving...
                </span>
            </button>
        </form>
    </div>

    <script>
        document.getElementById('profileForm').addEventListener('submit', function() {
            const btn = document.getElementById('saveBtn');
            const btnText = document.getElementById('saveBtnText');
            const btnSpinner = document.getElementById('saveBtnSpinner');
            btn.disabled = true;
            btn.classList.add('opacity-50', 'cursor-not-allowed');
            btnText.classList.add('hidden');
            btnSpinner.classList.remove('hidden');
        });
    </script>
@endsection
