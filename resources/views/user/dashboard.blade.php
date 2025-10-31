@extends('layouts.user')

@section('title', 'Voter Dashboard')

@section('content')
    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="bg-green-500 text-white px-4 py-2 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="bg-red-500 text-white px-4 py-2 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    {{-- Welcome Section --}}
    <div class="mb-8 relative">
        <h2 class="text-3xl font-bold text-white mb-2">
            Welcome back, {{ $user->name ?? 'Voter' }}!
        </h2>
        <p class="text-white-600">Ready to make your voice heard? Check out the elections below.</p>

        @php
            $statusText = $user->is_eligible ? 'Eligible' : 'Not Eligible';
            $statusColor = $user->is_eligible ? 'bg-green-500' : 'bg-red-500';
        @endphp
        <span
            class="absolute top-0 right-0 mt-2 mr-2 px-3 py-1 rounded-full text-sm font-semibold text-white {{ $statusColor }}">
            Voting Status: {{ $statusText }}
        </span>
    </div>

    {{-- Quick Stats Cards --}}
    <div class="flex flex-wrap gap-6 mb-8">
        {{-- Active Elections --}}
        <div onclick="showSection('active')"
            class="cursor-pointer flex-1 min-w-[150px] p-6 rounded-xl shadow-md border border-gray-700 bg-[#09182D] text-white transform transition-transform duration-200 hover:scale-105">
            <div class="flex flex-col">
                <p class="text-gray-300 text-sm font-medium">Active Elections</p>
                <p class="text-3xl font-bold text-right mt-1" style="text-shadow: 1px 1px 3px rgba(0,0,0,0.4);">
                    {{ $activeElections }}
                </p>
            </div>
        </div>

        {{-- Upcoming Elections --}}
        <div onclick="showSection('upcoming')"
            class="cursor-pointer flex-1 min-w-[150px] p-6 rounded-xl shadow-md border border-gray-700 bg-[#09182D] text-white transform transition-transform duration-200 hover:scale-105">
            <div class="flex flex-col">
                <p class="text-gray-300 text-sm font-medium">Upcoming Elections</p>
                <p class="text-3xl font-bold text-right mt-1" style="text-shadow: 1px 1px 3px rgba(0,0,0,0.4);">
                    {{ $upcomingElections }}
                </p>
            </div>
        </div>

        {{-- Closed Elections --}}
        <div onclick="showSection('closed')"
            class="cursor-pointer flex-1 min-w-[150px] p-6 rounded-xl shadow-md border border-gray-700 bg-[#09182D] text-white transform transition-transform duration-200 hover:scale-105">
            <div class="flex flex-col">
                <p class="text-gray-300 text-sm font-medium">Closed Elections</p>
                <p class="text-3xl font-bold text-right mt-1" style="text-shadow: 1px 1px 3px rgba(0,0,0,0.4);">
                    {{ $closedElections }}
                </p>
            </div>
        </div>

        {{-- Skipped Elections --}}
        <div onclick="showSection('skipped')"
            class="cursor-pointer flex-1 min-w-[150px] p-6 rounded-xl shadow-md border border-gray-700 bg-[#09182D] text-white transform transition-transform duration-200 hover:scale-105">
            <div class="flex flex-col">
                <p class="text-gray-300 text-sm font-medium">Skipped Elections</p>
                <p class="text-3xl font-bold text-right mt-1" style="text-shadow: 1px 1px 3px rgba(0,0,0,0.4);">
                    {{ $skippedElections }}
                </p>
            </div>
        </div>

        {{-- Votes Cast --}}
        <div onclick="showSection('votes')"
            class="cursor-pointer flex-1 min-w-[150px] p-6 rounded-xl shadow-md border border-gray-700 bg-[#09182D] text-white transform transition-transform duration-200 hover:scale-105">
            <div class="flex flex-col">
                <p class="text-gray-300 text-sm font-medium">Votes Cast</p>
                <p class="text-3xl font-bold text-right mt-1" style="text-shadow: 1px 1px 3px rgba(0,0,0,0.4);">
                    {{ $userVotesCount }}
                </p>
            </div>
        </div>
    </div>


    {{-- Election Sections --}}
    <div class="mb-8">
        {{-- Active Elections --}}
        <div id="active-section">
            <h3 class="text-2xl font-bold text-white mb-6">Active Elections</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($activeElectionsList->unique('id')->sortByDesc('start_date') as $election)
                    <div class="p-6 rounded-xl shadow-sm border border-gray-700 bg-[#10243F] hover:shadow-lg transition">
                        <h4 class="text-xl font-bold text-yellow-400">{{ $election->title }}</h4>
                        <p class="text-gray-300 mt-2">{{ $election->description }}</p>
                        <p class="text-sm text-gray-400 mt-2">
                            Ends: <span id="countdown-{{ $election->id }}"></span>
                        </p>
                        @php
                            $hasVoted = $user->votes->contains('election_id', $election->id);
                        @endphp

                        @if ($user->is_eligible && !$hasVoted)
                            <a href="{{ route('user.elections.show', $election->id) }}" id="vote-btn-{{ $election->id }}"
                                class="mt-4 inline-block px-4 py-2 bg-yellow-500 text-[#09182D] font-semibold rounded-lg hover:bg-yellow-400 transition">
                                Vote Now
                            </a>
                        @elseif($hasVoted)
                            <button type="button"
                                class="mt-4 inline-block px-4 py-2 bg-gray-500 text-gray-300 font-semibold rounded-lg cursor-not-allowed"
                                disabled>
                                Already Voted
                            </button>
                        @else
                            <button type="button"
                                class="mt-4 inline-block px-4 py-2 bg-gray-500 text-gray-300 font-semibold rounded-lg cursor-not-allowed"
                                disabled>
                                Not Eligible
                            </button>
                        @endif
                    </div>
                @empty
                    <p class="text-gray-400 col-span-full text-center">No active elections right now.</p>
                @endforelse
            </div>
        </div>

        {{-- Upcoming Elections --}}
        <div id="upcoming-section" class="hidden">
            <h3 class="text-2xl font-bold text-white mb-6">Upcoming Elections</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($upcomingElectionsList->sortByDesc('start_date') as $election)
                    <div class="p-6 rounded-xl shadow-sm border border-gray-700 bg-[#10243F] hover:shadow-lg transition">
                        <h4 class="text-xl font-bold text-blue-400">{{ $election->title }}</h4>
                        <p class="text-gray-300 mt-2">{{ $election->description }}</p>
                        <p class="text-sm text-gray-400 mt-2">
                            Starts: {{ \Carbon\Carbon::parse($election->start_date)->format('M d, Y h:i A') }}
                        </p>
                    </div>
                @empty
                    <p class="text-gray-400 col-span-full text-center">No upcoming elections.</p>
                @endforelse
            </div>
        </div>

        {{-- Closed Elections --}}
        <div id="closed-section" class="hidden">
            <h3 class="text-2xl font-bold text-white mb-6">Closed Elections</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($closedElectionsList->sortByDesc('end_date') as $election)
                    <div class="p-6 rounded-xl shadow-sm border border-gray-700 bg-[#10243F] hover:shadow-lg transition">
                        <h4 class="text-xl font-bold text-red-400">{{ $election->title }}</h4>
                        <p class="text-gray-300 mt-2">{{ $election->description }}</p>
                        <p class="text-sm text-gray-400 mt-2">
                            Start: {{ \Carbon\Carbon::parse($election->start_date)->format('M d, Y h:i A') }}<br>
                            End: {{ \Carbon\Carbon::parse($election->end_date)->format('M d, Y h:i A') }}
                        </p>
                    </div>
                @empty
                    <p class="text-gray-400 col-span-full text-center">No closed elections.</p>
                @endforelse
            </div>
        </div>

        {{-- Skipped Elections --}}
        <div id="skipped-section" class="hidden mb-8">
            <h3 class="text-2xl font-bold text-white mb-6">Skipped Elections</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($skippedElectionsList->sortByDesc('end_date') as $election)
                    <div class="p-6 rounded-xl shadow-sm border border-gray-700 bg-[#10243F] hover:shadow-lg transition cursor-pointer"
                        onclick="window.location='{{ route('user.elections.show', $election->id) }}'">
                        <h4 class="text-xl font-bold text-red-400">{{ $election->title }}</h4>
                        <p class="text-gray-300 mt-2">{{ $election->description }}</p>
                        <p class="text-sm text-gray-400 mt-2">
                            Start: {{ \Carbon\Carbon::parse($election->start_date)->format('M d, Y h:i A') }}<br>
                            End: {{ \Carbon\Carbon::parse($election->end_date)->format('M d, Y h:i A') }}
                        </p>
                        <span
                            class="mt-2 inline-block px-2 py-1 text-xs font-semibold text-white bg-red-600 rounded-full">Skipped</span>
                    </div>
                @empty
                    <p class="text-gray-400 col-span-full text-center">You have not skipped any elections.</p>
                @endforelse
            </div>
        </div>



        {{-- ✅ Votes Cast Section --}}
        <div id="votes-section" class="hidden mb-8">
            <h3 class="text-2xl font-bold text-white mb-6">Votes Cast</h3>

            @php
                // Group votes by election_id and sort in descending order (latest first)
                $groupedVotes = $user->votes->sortByDesc('created_at')->groupBy('election_id');
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($groupedVotes as $electionId => $votes)
                    @php
                        $election = $votes->first()->election;
                    @endphp

                    <div class="p-6 rounded-xl shadow-sm border border-gray-700 bg-[#10243F] hover:shadow-lg transition">
                        {{-- Election Title --}}
                        <h4 class="text-xl font-bold text-green-400">
                            {{ $election->title ?? 'Unknown Election' }}
                        </h4>

                        {{-- ✅ List all candidates voted for in this election --}}
                        <p class="text-gray-300 mt-2">
                            <span class="font-semibold text-yellow-400">Candidates:</span><br>
                            @foreach ($votes as $vote)
                                <span class="block ml-2">• {{ $vote->candidate->name ?? 'N/A' }}</span>
                            @endforeach
                        </p>

                        {{-- Date and Time --}}
                        <p class="text-sm text-gray-400 mt-3">
                            <span class="font-semibold text-gray-300">Voted at:</span>
                            {{ $votes->first()->created_at->timezone('Asia/Manila')->format('M d, Y h:i A') }}
                        </p>

                        {{-- Download Cast Button --}}
                        <div class="mt-4">
                            <a href="{{ route('user.vote.downloadPDF', ['election' => $electionId]) }}"
                                class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded text-sm font-semibold">
                                Download Cast
                            </a>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-400 col-span-full text-center">You haven’t voted yet.</p>
                @endforelse
            </div>
        </div>



        {{-- Voting History --}}
        <div class="mb-8">
            <h3 class="text-2xl font-bold text-white-900 mb-6">Your Voting History</h3>
            <ul id="voting-history" class="list-disc pl-6 text-gray-700">
                @forelse($user->votes->sortByDesc('created_at') as $vote)
                    <li>Voted in <span class="font-semibold">{{ $vote->election->title }}</span> on
                        {{ $vote->created_at->timezone('Asia/Manila')->format('M d, Y h:i:s A') }}
                    </li>
                @empty
                    <li class="text-gray-500">You haven’t voted yet.</li>
                @endforelse
            </ul>
        </div>

        {{-- Scripts --}}
        <script>
            function showSection(section) {
                document.getElementById('active-section').classList.add('hidden');
                document.getElementById('upcoming-section').classList.add('hidden');
                document.getElementById('closed-section').classList.add('hidden');
                document.getElementById('skipped-section').classList.add('hidden');
                document.getElementById('votes-section').classList.add('hidden');
                document.getElementById(section + '-section').classList.remove('hidden');
            }

            // Countdown for active elections
            @foreach ($activeElectionsList as $election)
                var countdownEl{{ $election->id }} = document.getElementById('countdown-{{ $election->id }}');
                var endTime{{ $election->id }} = new Date("{{ $election->end_date }}").getTime();

                var x{{ $election->id }} = setInterval(function() {
                    var now = new Date().getTime();
                    var distance = endTime{{ $election->id }} - now;

                    if (distance > 0) {
                        var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                        var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                        var seconds = Math.floor((distance % (1000 * 60)) / 1000);
                        countdownEl{{ $election->id }}.innerHTML = hours + "h " + minutes + "m " + seconds + "s ";
                    } else {
                        countdownEl{{ $election->id }}.innerHTML = "Election Closed";
                        clearInterval(x{{ $election->id }});
                        var voteBtn = document.getElementById('vote-btn-{{ $election->id }}');
                        if (voteBtn) voteBtn.disabled = true;
                        if (voteBtn) voteBtn.classList.add('bg-gray-500', 'cursor-not-allowed');
                        if (voteBtn) voteBtn.classList.remove('bg-yellow-500', 'hover:bg-yellow-400');
                    }
                }, 1000);
            @endforeach
        </script>
    @endsection
