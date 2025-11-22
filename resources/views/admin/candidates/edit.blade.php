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
        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-500 transition">
            Update Candidate
        </button>
    </form>
@endsection
