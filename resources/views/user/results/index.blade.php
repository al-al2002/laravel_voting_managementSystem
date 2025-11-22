@extends('layouts.user')

@section('title', 'Election Results')

@section('content')
    <div class="container mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-yellow-400">Election Results</h1>

            <a href="{{ route('user.dashboard') }}"
                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-semibold transition flex items-center gap-2">
                &times; Close
            </a>
        </div>

        @php
            $closedElections = $elections->filter(fn($e) => $e->isClosed());
        @endphp

        @forelse($closedElections as $election)
            <div class="card mb-6 p-4 rounded-lg shadow bg-[#10243F] text-white">
                <h2 class="text-xl font-bold text-blue-400 mb-2">{{ $election->title }}</h2>

                <p class="text-sm text-gray-300 mb-2">
                    <strong>Start:</strong> {{ \Carbon\Carbon::parse($election->start_date)->format('F d, Y h:i A') }} <br>
                    <strong>End:</strong> {{ \Carbon\Carbon::parse($election->end_date)->format('F d, Y h:i A') }}
                </p>

                <p class="mb-2">Status: <span class="text-red-500 font-semibold">Closed</span></p>

                @php
                    $totalVotes = $election->candidates->sum('votes_count');
                @endphp
                <p class="mb-2"><strong>Total Votes:</strong> {{ $totalVotes }}</p>

                {{-- Winners per position --}}
                <h4 class="font-semibold mt-4 text-yellow-400">Winners:</h4>
                @php
                    $grouped = $election->candidates->groupBy('position');
                @endphp

                @foreach ($grouped as $position => $candidates)
                    @php
                        $maxVotes = $candidates->max('votes_count');
                        $winners = $candidates->where('votes_count', $maxVotes);
                    @endphp

                    <p class="mt-2 font-bold text-lg text-yellow-400">
                        {{ ucfirst($position ?? 'No Position') }}
                        {{ $winners->count() > 1 ? '(Tie)' : '' }}:
                    </p>
                    <ul class="list-disc list-inside text-yellow-300 font-semibold mb-3">
                        @foreach ($winners as $winner)
                            <li>🎉 {{ $winner->name }} — {{ $winner->votes_count }}
                                vote{{ $winner->votes_count > 1 ? 's' : '' }}</li>
                        @endforeach
                    </ul>
                @endforeach

                {{-- Candidate Table --}}
                <h4 class="mt-4 font-semibold text-white">Candidates:</h4>
                <table class="min-w-full mt-2 border border-gray-700 text-white">
                    <thead>
                        <tr class="bg-gray-800">
                            <th class="px-4 py-2 border border-gray-700">Photo</th>
                            <th class="px-4 py-2 border border-gray-700">Name</th>
                            <th class="px-4 py-2 border border-gray-700">Position</th>
                            <th class="px-4 py-2 border border-gray-700">Votes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($election->candidates as $candidate)
                            <tr @if ($candidate->votes_count == $grouped[$candidate->position]->max('votes_count')) class="bg-yellow-900 font-bold" @endif>
                                <td class="px-4 py-2 border border-gray-700">
                                    @if ($candidate->photo)
                                        <img src="{{ $candidate->photo_url ?? asset('images/default-candidate.png') }}"
                                            class="w-12 h-12 rounded-full object-cover">
                                    @else
                                        <span class="text-gray-400">No photo</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 border border-gray-700">{{ $candidate->name }}</td>
                                <td class="px-4 py-2 border border-gray-700">{{ $candidate->position ?? 'N/A' }}</td>
                                <td class="px-4 py-2 border border-gray-700">{{ $candidate->votes_count ?? 0 }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @empty
            <p class="text-gray-400">No closed election results available.</p>
        @endforelse
    </div>
@endsection
