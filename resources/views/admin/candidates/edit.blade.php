@extends('layouts.admin')

@section('title', 'Edit Candidate')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Edit Candidate</h1>

    <form action="{{ route('admin.candidates.update', $candidate->id) }}" method="POST" enctype="multipart/form-data"
        class="space-y-4">
        @csrf
        @method('PUT')

        {{-- Name --}}
        <div>
            <label class="block font-medium mb-1">Name</label>
            <input type="text" name="name" value="{{ old('name', $candidate->name) }}"
                class="w-full border rounded-lg px-3 py-2" required>
        </div>

        {{-- Position --}}
        <div>
            <label class="block font-medium mb-1">Position</label>
            <input type="text" name="position" value="{{ old('position', $candidate->position) }}"
                class="w-full border rounded-lg px-3 py-2" required>
        </div>

        {{-- Election --}}
        <div>
            <label class="block font-medium mb-1">Election</label>
            <select name="election_id" class="w-full border rounded-lg px-3 py-2" required>
                @foreach ($elections as $election)
                    <option value="{{ $election->id }}" {{ $candidate->election_id == $election->id ? 'selected' : '' }}>
                        {{ $election->title }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Photo --}}
        <div>
            <label class="block font-medium mb-1">Photo</label>
            @if ($candidate->photo)
                <img src="{{ $candidate->photo_url ?? asset('images/default-candidate.png') }}"
                    class="w-20 h-20 rounded-full object-cover mb-2">
            @endif
            <input type="file" name="photo" accept="image/*" class="w-full border rounded-lg px-3 py-2">
            <p class="text-gray-500 text-sm mt-1">Leave blank if you don’t want to change the photo.</p>
        </div>

        {{-- Submit --}}
        <button type="submit" id="updateCandidateBtn"
            class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-500 transition">
            <span id="updateCandidateText">Update Candidate</span>
            <span id="updateCandidateSpinner" class="hidden">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                    </circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                Updating...
            </span>
        </button>
    </form>

    <script>
        // Loading state for update candidate form
        document.querySelector('form').addEventListener('submit', function() {
            const btn = document.getElementById('updateCandidateBtn');
            const btnText = document.getElementById('updateCandidateText');
            const btnSpinner = document.getElementById('updateCandidateSpinner');
            btn.disabled = true;
            btn.classList.add('opacity-50', 'cursor-not-allowed');
            btnText.classList.add('hidden');
            btnSpinner.classList.remove('hidden');
        });
    </script>
@endsection
