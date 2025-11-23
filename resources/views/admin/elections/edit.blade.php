@extends('layouts.admin')

@section('title', 'Edit Election')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Edit Election</h1>

    <form id="editElectionForm" action="{{ route('admin.elections.update', $election->id) }}" method="POST" class="space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label class="block font-medium">Title</label>
            <input type="text" name="title" value="{{ old('title', $election->title) }}"
                class="w-full border rounded-lg px-3 py-2">
            @error('title')
                <p class="text-red-500 text-sm">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block font-medium">Description</label>
            <textarea name="description" rows="4" class="w-full border rounded-lg px-3 py-2">{{ old('description', $election->description) }}</textarea>
            @error('description')
                <p class="text-red-500 text-sm">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block font-medium">Start Date & Time</label>
            <input type="datetime-local" name="start_date"
                value="{{ old('start_date', $election->start_date->format('Y-m-d\TH:i')) }}"
                class="w-full border rounded-lg px-3 py-2">
            @error('start_date')
                <p class="text-red-500 text-sm">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block font-medium">End Date & Time</label>
            <input type="datetime-local" name="end_date"
                value="{{ old('end_date', $election->end_date->format('Y-m-d\TH:i')) }}"
                class="w-full border rounded-lg px-3 py-2">
            @error('end_date')
                <p class="text-red-500 text-sm">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" id="updateBtn"
            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 inline-flex items-center">
            <span id="updateBtnText">Update Election</span>
            <span id="updateBtnSpinner" class="hidden ml-2">
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
        document.getElementById('editElectionForm').addEventListener('submit', function() {
            const btn = document.getElementById('updateBtn');
            const btnText = document.getElementById('updateBtnText');
            const btnSpinner = document.getElementById('updateBtnSpinner');
            btn.disabled = true;
            btn.classList.add('opacity-75', 'cursor-not-allowed');
            btnText.textContent = 'Updating...';
            btnSpinner.classList.remove('hidden');
        });
    </script>
@endsection
