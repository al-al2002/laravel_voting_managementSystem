@extends('layouts.admin')

@section('title', 'Create Election')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Create New Election</h1>

    <form id="createElectionForm" action="{{ route('admin.elections.store') }}" method="POST" class="space-y-4">
        @csrf

        <div>
            <label class="block font-medium">Title</label>
            <input type="text" name="title" value="{{ old('title') }}" class="w-full border rounded-lg px-3 py-2">
            @error('title')
                <p class="text-red-500 text-sm">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block font-medium">Description</label>
            <textarea name="description" rows="4" class="w-full border rounded-lg px-3 py-2">{{ old('description') }}</textarea>
            @error('description')
                <p class="text-red-500 text-sm">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block font-medium">Start Date & Time</label>
            <input type="datetime-local" name="start_date" value="{{ old('start_date') }}"
                class="w-full border rounded-lg px-3 py-2">
            @error('start_date')
                <p class="text-red-500 text-sm">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block font-medium">End Date & Time</label>
            <input type="datetime-local" name="end_date" value="{{ old('end_date') }}"
                class="w-full border rounded-lg px-3 py-2">
            @error('end_date')
                <p class="text-red-500 text-sm">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" id="createBtn"
            class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 inline-flex items-center">
            <span id="createBtnText">Create Election</span>
            <span id="createBtnSpinner" class="hidden ml-2">
                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                    </circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
            </span>
        </button>
    </form>

    <script>
        document.getElementById('createElectionForm').addEventListener('submit', function() {
            const btn = document.getElementById('createBtn');
            const btnText = document.getElementById('createBtnText');
            const btnSpinner = document.getElementById('createBtnSpinner');
            btn.disabled = true;
            btn.classList.add('opacity-75', 'cursor-not-allowed');
            btnText.textContent = 'Creating...';
            btnSpinner.classList.remove('hidden');
        });
    </script>
@endsection
