@extends('layouts.admin')

@section('title', 'Manage Candidates')

@section('content')
    <h1 class="text-2xl font-bold mb-6 text-[#09182D]">Candidates</h1>

    <div class="flex justify-between items-center mb-6 gap-4">
        <a href="{{ route('admin.candidates.create') }}"
            class="bg-[#09182D] text-white px-4 py-2 rounded-lg hover:bg-[#0f2345] transition">
            + Add Candidate
        </a>

        {{-- Filter --}}
        <form method="GET" action="{{ route('admin.candidates.index') }}">
            <select name="status" onchange="this.form.submit()"
                class="border border-[#09182D] rounded-lg px-3 py-2 text-[#09182D]">
                <option value="" {{ request('status') === null || request('status') === '' ? 'selected' : '' }}>All
                    Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="upcoming" {{ request('status') === 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
            </select>
        </form>

    </div>

    @if ($elections->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($elections as $election)
                @php
                    $now = \Carbon\Carbon::now();
                    if ($election->start_date > $now) {
                        $statusClass = 'bg-yellow-200 text-yellow-800';
                        $statusText = 'Upcoming';
                        $editable = true;
                    } elseif ($election->end_date < $now) {
                        $statusClass = 'bg-red-200 text-red-800';
                        $statusText = 'Closed';
                        $editable = false;
                    } else {
                        $statusClass = 'bg-green-200 text-green-800';
                        $statusText = 'Active';
                        $editable = true;
                    }
                @endphp

                <div
                    class="bg-white rounded-2xl shadow-lg p-6 transform hover:scale-105 transition duration-300 border-t-4 border-[#09182D]">
                    {{-- Election Title --}}
                    <h2 class="text-xl font-bold text-[#09182D] mb-2">{{ $election->title }}</h2>

                    {{-- Election Status --}}
                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">
                        {{ $statusText }}
                    </span>

                    {{-- Candidates List --}}
                    <h4 class="mt-4 font-semibold text-[#09182D]">Candidates:</h4>
                    <ul class="mt-2 space-y-2">
                        @forelse($election->candidates as $candidate)
                            <li class="flex items-center justify-between p-2 bg-gray-50 rounded-lg shadow-sm">
                                <div class="flex items-center gap-3">
                                    @if ($candidate->photo)
                                        <img src="{{ $candidate->photo_url ?? asset('images/default-candidate.png') }}"
                                            alt="{{ $candidate->name }}" class="w-10 h-10 rounded-full object-cover">
                                    @else
                                        <div
                                            class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center text-gray-500">
                                            N/A
                                        </div>
                                    @endif
                                    <div>
                                        <p class="font-medium text-[#09182D]">{{ $candidate->name }}</p>
                                        <p class="text-sm text-gray-500">{{ $candidate->position }}</p>
                                    </div>
                                </div>

                                @if ($editable)
                                    <div class="flex gap-2">
                                        <a href="{{ route('admin.candidates.edit', $candidate->id) }}"
                                            class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600 transition">
                                            Edit
                                        </a>

                                        <form action="{{ route('admin.candidates.destroy', $candidate->id) }}"
                                            method="POST" class="delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 transition">
                                                Remove
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </li>
                        @empty
                            <li class="text-gray-400">No candidates yet</li>
                        @endforelse
                    </ul>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-center text-gray-500 mt-6">No elections found.</p>
    @endif

    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Delete confirmation
        document.querySelectorAll('.delete-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This candidate will be removed!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, remove it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) form.submit();
                });
            });
        });

        // Show success/error toast
        document.addEventListener('DOMContentLoaded', function() {
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: "{{ session('success') }}",
                    timer: 1500,
                    showConfirmButton: false,
                    position: 'top-end',
                    toast: true
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: "{{ session('error') }}",
                    timer: 1500,
                    showConfirmButton: false,
                    position: 'top-end',
                    toast: true
                });
            @endif
        });
    </script>
@endsection
