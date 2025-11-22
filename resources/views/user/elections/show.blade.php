@extends('layouts.user')

@section('title', $election->title ?? 'Election')

@section('content')
    <div class="max-w-5xl mx-auto bg-[#10243F] text-white shadow rounded-xl p-8 relative">

        {{-- Back Button --}}
        <div class="absolute top-4 left-4">
            <a href="{{ route('user.dashboard') }}"
                class="inline-block bg-gray-700 hover:bg-gray-600 text-white px-5 py-2 rounded-lg font-semibold transition">
                ← Back to Dashboard
            </a>
        </div>

        {{-- Election Info --}}
        <div class="text-center mb-10">
            <h2 class="text-4xl font-extrabold text-yellow-400 mb-3">{{ $election->title }}</h2>
            <p class="text-gray-300 text-lg max-w-2xl mx-auto">{{ $election->description }}</p>
            <p class="text-sm text-gray-400 mt-2">
                {{ \Carbon\Carbon::parse($election->start_date)->format('M d, Y h:i A') }} -
                {{ \Carbon\Carbon::parse($election->end_date)->format('M d, Y h:i A') }}
            </p>
            <p class="text-green-400 text-lg mt-2">Time Remaining: <span id="countdown"></span></p>
        </div>

        {{-- Candidates Section --}}
        <h3 class="text-2xl font-semibold mb-6 text-center">Select Candidates</h3>

        @php
            // ✅ Group candidates by position
            $groupedCandidates = $candidates->groupBy('position');
        @endphp

        <form id="voteForm">
            @csrf

            @foreach ($groupedCandidates as $position => $candidatesByPosition)
                {{-- Position Heading --}}
                <div class="mb-6">
                    <h4 class="text-xl font-bold text-yellow-400 border-b border-gray-600 pb-2 mb-4">
                        {{ strtoupper($position ?? 'Other Positions') }}
                    </h4>

                    {{-- Candidates Grid --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                        @foreach ($candidatesByPosition as $candidate)
                            <label for="candidate-{{ $candidate->id }}"
                                class="relative cursor-pointer bg-[#09182D] border border-gray-700 rounded-2xl p-6 text-center hover:scale-105 transition block">

                                {{-- Hidden Checkbox --}}
                                <input type="checkbox" name="candidate_ids[]" id="candidate-{{ $candidate->id }}"
                                    value="{{ $candidate->id }}" class="absolute opacity-0 peer">

                                {{-- Candidate Image --}}
                                <div class="relative w-28 h-28 mx-auto mb-4">
                                    <img src="{{ $candidate->photo_url ?? 'https://via.placeholder.com/150' }}"
                                        alt="{{ $candidate->name }}"
                                        class="w-28 h-28 rounded-full mx-auto object-cover border-4 border-gray-600 peer-checked:border-yellow-400 transition">
                                </div>

                                {{-- Candidate Info --}}
                                <h4 class="text-lg font-bold">{{ $candidate->name }}</h4>
                                <p class="text-gray-400 text-sm">{{ $candidate->position ?? 'Candidate' }}</p>

                                {{-- Custom Circle Indicator --}}
                                <div
                                    class="w-6 h-6 rounded-full border-2 border-gray-400 mx-auto mt-4 flex items-center justify-center peer-checked:border-yellow-400 peer-checked:bg-yellow-400">
                                    <svg class="w-4 h-4 text-[#09182D] opacity-0 peer-checked:opacity-100"
                                        fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414L8.414 15l-4.121-4.121a1 1 0 011.414-1.414L8.414 12.172l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach

            {{-- Submit Button --}}
            <div class="text-center mt-10">
                <button type="button" id="submitVote"
                    class="bg-yellow-400 text-[#09182D] px-8 py-3 rounded-lg font-semibold hover:bg-yellow-300 transition">
                    Submit Vote
                </button>
            </div>
        </form>
    </div>

    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.getElementById('submitVote').addEventListener('click', function() {
            const selected = document.querySelectorAll('input[name="candidate_ids[]"]:checked');
            if (selected.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No candidate selected',
                    text: 'Please select at least one candidate before submitting.'
                });
                return;
            }

            Swal.fire({
                title: 'Confirm Your Votes?',
                html: `<p>You have selected <strong>${selected.length}</strong> candidate(s).</p>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Submit',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData(document.getElementById('voteForm'));
                    fetch("{{ route('user.elections.vote', $election->id) }}", {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                                'Accept': 'application/json'
                            },
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Vote Submitted!',
                                    text: data.message,
                                    timer: 2000,
                                    showConfirmButton: false,
                                    willClose: () => {
                                        window.location.href =
                                            "{{ route('user.dashboard') }}";
                                    }
                                });

                                document.querySelectorAll('input[name="candidate_ids[]"]').forEach(i =>
                                    i.disabled = true);
                                const btn = document.getElementById('submitVote');
                                btn.disabled = true;
                                btn.classList.add('bg-gray-500', 'cursor-not-allowed');
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops!',
                                    text: data.message || 'Something went wrong.'
                                });
                            }
                        })
                        .catch(error => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to submit your vote. Please try again.'
                            });
                            console.error(error);
                        });
                }
            });
        });

        // Countdown Timer
        const countdownEl = document.getElementById('countdown');
        const endTime = new Date("{{ $election->end_date }}").getTime();

        const x = setInterval(function() {
            const now = new Date().getTime();
            const distance = endTime - now;

            if (distance > 0) {
                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                countdownEl.innerHTML = `${days}d ${hours}h ${minutes}m ${seconds}s`;
            } else {
                countdownEl.innerHTML = "Election Closed";
                clearInterval(x);
                document.querySelectorAll('input[name="candidate_ids[]"]').forEach(i => i.disabled = true);
                document.getElementById('submitVote').disabled = true;
                document.getElementById('submitVote').classList.add('bg-gray-500', 'cursor-not-allowed');
            }
        }, 1000);
    </script>
@endsection
