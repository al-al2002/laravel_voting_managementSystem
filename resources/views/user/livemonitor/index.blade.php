@extends('layouts.user')

@section('title', 'Live Monitor')

@section('content')
    <div class="container mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-yellow-400">Live Monitor</h1>
            <a href="{{ route('user.dashboard') }}"
                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-semibold transition flex items-center gap-2">
                &times; Close
            </a>
        </div>

        @forelse($activeElections as $election)
            <div class="bg-[#10243F] border border-gray-700 rounded-lg p-6 mb-6 shadow">
                {{-- Election Title --}}
                <p class="text-lg font-semibold mb-4">
                    Active Election: <span class="text-yellow-400">{{ $election->title }}</span>
                </p>

                {{-- Group by Position --}}
                @php
                    // Group candidates by position
                    $groupedCandidates = $election->candidates->groupBy('position');
                @endphp

                @foreach ($groupedCandidates as $position => $candidates)
                    {{-- Position Header --}}
                    <h2 class="text-xl font-bold text-blue-400 mb-3 mt-6 border-b border-gray-600 pb-1">
                        {{ ucfirst($position) ?? 'Unknown Position' }}
                    </h2>

                    {{-- Sort candidates by votes descending --}}
                    @php
                        $sortedCandidates = $candidates->sortByDesc('votes_count');
                    @endphp

                    <ul class="space-y-2">
                        @foreach ($sortedCandidates as $candidate)
                            <li
                                class="flex justify-between items-center p-3 border border-gray-600 rounded-lg bg-[#09182D] hover:bg-[#0f2445] transition">
                                <div class="flex items-center space-x-3">
                                    {{-- Candidate Photo --}}
                                    @if ($candidate->photo)
                                        <img src="{{ $candidate->photo_url ?? asset('images/default-candidate.png') }}"
                                            alt="{{ $candidate->name }}"
                                            class="w-10 h-10 rounded-full object-cover border-2 border-gray-500">
                                    @else
                                        <div
                                            class="w-10 h-10 rounded-full bg-gray-600 flex items-center justify-center text-gray-300 text-sm font-semibold">
                                            N/A
                                        </div>
                                    @endif

                                    {{-- Candidate Name --}}
                                    <span class="font-medium text-white">{{ $candidate->name }}</span>
                                </div>

                                {{-- Live Votes --}}
                                <span class="font-bold text-yellow-400 text-lg">{{ $candidate->votes_count ?? 0 }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endforeach
            </div>
        @empty
            <p class="text-gray-400">No active elections at the moment.</p>
        @endforelse
    </div>
@endsection
