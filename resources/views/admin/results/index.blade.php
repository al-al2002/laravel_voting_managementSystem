@extends('layouts.admin')

@section('title', 'Election Results')

@section('content')
    <div class="container mx-auto py-6">
        <h1 class="text-2xl font-bold mb-6">Closed Election Results</h1>

        @forelse($elections as $election)
            <div class="bg-white p-6 rounded-lg shadow mb-6">

                {{-- Election Title --}}
                <h2 class="text-2xl font-bold mb-2 text-[#09182D]">{{ $election->title }}</h2>

                {{-- Dates --}}
                <p class="text-sm text-gray-600">
                    <strong>Start:</strong> {{ \Carbon\Carbon::parse($election->start_date)->format('F d, Y h:i A') }} <br>
                    <strong>End:</strong> {{ \Carbon\Carbon::parse($election->end_date)->format('F d, Y h:i A') }}
                </p>

                {{-- Status --}}
                <p class="mt-2">
                    Status: <span class="text-red-600 font-semibold">Closed</span>
                </p>

                {{-- Total Votes --}}
                <p class="mt-1"><strong>Total Votes:</strong> {{ $election->total_votes }}</p>

                {{-- Winners grouped by position --}}
                <h4 class="mt-3 font-semibold">🏆 Winners by Position:</h4>

                @php
                    $groupedByPosition = $election->candidates->groupBy('position');
                @endphp

                @foreach ($groupedByPosition as $position => $candidates)
                    @php
                        $maxVotes = $candidates->max('votes_count');
                        $winners = $candidates->where('votes_count', $maxVotes);
                        $isTie = $winners->count() > 1;
                    @endphp

                    <div class="mt-3">
                        <h5 class="text-lg font-semibold text-[#09182D]">
                            {{ $position }} {{ $isTie ? '(Tie)' : '' }}
                        </h5>

                        <ul class="list-disc list-inside text-yellow-600 font-bold">
                            @foreach ($winners as $winner)
                                <li>
                                    🎉 {{ $winner->name }} —
                                    <span class="text-[#09182D]">{{ $winner->votes_count }} votes</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach

                {{-- Candidates Table --}}
                <h4 class="mt-5 font-semibold">Candidates:</h4>
                <table class="min-w-full mt-2 border border-gray-200">
                    <thead>
                        <tr class="bg-gray-100 text-gray-700">
                            <th class="px-4 py-2 border">Photo</th>
                            <th class="px-4 py-2 border">Name</th>
                            <th class="px-4 py-2 border">Position</th>
                            <th class="px-4 py-2 border">Votes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($election->candidates as $candidate)
                            <tr>
                                <td class="px-4 py-2 border text-center">
                                    @if ($candidate->photo)
                                        <img src="{{ $candidate->photo_url ?? asset('images/default-candidate.png') }}"
                                            alt="{{ $candidate->name }}"
                                            class="w-12 h-12 rounded-full object-cover mx-auto">
                                    @else
                                        <span class="text-gray-400">No photo</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 border text-[#09182D]">{{ $candidate->name }}</td>
                                <td class="px-4 py-2 border text-[#09182D]">{{ $candidate->position }}</td>
                                <td class="px-4 py-2 border text-center text-[#09182D]">{{ $candidate->votes_count ?? 0 }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @empty
            <div class="bg-red-100 text-red-600 p-4 rounded-lg shadow text-center">
                <p>No closed elections found.</p>
            </div>
        @endforelse
    </div>
@endsection
