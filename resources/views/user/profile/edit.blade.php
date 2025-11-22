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

        <form action="{{ route('user.profile.update') }}" method="POST" enctype="multipart/form-data">
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

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                Save Changes
            </button>
        </form>
    </div>
@endsection
