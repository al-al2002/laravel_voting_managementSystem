@extends('layouts.admin')

@section('title', 'Dashboard')

@section('header-actions')
    <div class="flex items-center gap-4">
        <p class="text-gray-700 font-medium text-lg">
            👋 Hi, {{ App\Models\Admin::find(session('auth_id'))->name }}
        </p>
    </div>
@endsection

@section('content')
    {{-- Dashboard Cards --}}
    <div class="flex flex-wrap justify-between gap-6 mb-8">
        {{-- Active Elections --}}
        <div onclick="openModal('activeModal')"
            class="cursor-pointer flex-1 min-w-[200px] p-6 rounded-2xl shadow-xl border border-gray-100
                   bg-gradient-to-r from-green-400 to-indigo-500 text-white transform hover:scale-105
                   transition duration-300">
            <div class="flex flex-col">
                <p class="text-sm font-medium opacity-80">Active Elections</p>
                <p class="text-3xl font-bold text-right mt-2" style="text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.4);">
                    {{ $activeElections }}
                </p>
            </div>
        </div>

        {{-- Closed Elections --}}
        <div onclick="openModal('closedModal')"
            class="cursor-pointer flex-1 min-w-[200px] p-6 rounded-2xl shadow-xl border border-gray-100
                   bg-gradient-to-r from-red-400 to-pink-500 text-white transform hover:scale-105
                   transition duration-300">
            <div class="flex flex-col">
                <p class="text-sm font-medium opacity-80">Closed Elections</p>
                <p class="text-3xl font-bold text-right mt-2" style="text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.4);">
                    {{ $closedElections }}
                </p>
            </div>
        </div>

        {{-- Upcoming Elections --}}
        <div onclick="openModal('upcomingModal')"
            class="cursor-pointer flex-1 min-w-[200px] p-6 rounded-2xl shadow-xl border border-gray-100
                   bg-gradient-to-r from-blue-500 to-teal-500 text-white transform hover:scale-105
                   transition duration-300">
            <div class="flex flex-col">
                <p class="text-sm font-medium opacity-80">Upcoming Elections</p>
                <p class="text-3xl font-bold text-right mt-2" style="text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.4);">
                    {{ $upcomingElections }}
                </p>
            </div>
        </div>

        {{-- Total Voters --}}
        <div onclick="openModal('votersModal')"
            class="cursor-pointer flex-1 min-w-[200px] p-6 rounded-2xl shadow-xl border border-gray-100
                   bg-gradient-to-r from-yellow-400 to-orange-500 text-white transform hover:scale-105
                   transition duration-300">
            <div class="flex flex-col">
                <p class="text-sm font-medium opacity-80">Total Voters</p>
                <p class="text-3xl font-bold text-right mt-2" style="text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.4);">
                    {{ $totalVoters }}
                </p>
            </div>
        </div>
    </div>
    {{-- Chart Section --}}
    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6 mb-10">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">Votes Per Election</h3>
        <canvas id="votesChart" height="150"></canvas>
    </div>

    {{-- Active Elections Modal --}}
    <div id="activeModal" class="hidden fixed inset-0 bg-black/50 z-50">
        <div class="flex items-center justify-center w-full h-full">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-3xl p-6 max-h-[80vh] overflow-y-auto relative">

                <!-- Red Close button fixed at top-right -->
                <button onclick="closeModal('activeModal')"
                    class="sticky top-0 right-0 float-right bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg z-50 ml-auto mb-4">
                    ✖
                </button>

                <h2 class="text-xl font-bold mb-4 text-blue-600">🟢 Active Elections</h2>
                @if ($activeList->isNotEmpty())
                    <table class="min-w-full border border-gray-200 rounded-lg">
                        <thead class="bg-gray-100 text-gray-700">
                            <tr>
                                <th class="px-4 py-2 text-left">Title</th>
                                <th class="px-4 py-2 text-left">Start Date</th>
                                <th class="px-4 py-2 text-left">End Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($activeList as $election)
                                <tr class="border-t hover:bg-gray-50">
                                    <td class="px-4 py-2">{{ $election->title }}</td>
                                    <td class="px-4 py-2">
                                        {{ \Carbon\Carbon::parse($election->start_date)->format('M d, Y h:i A') }}</td>
                                    <td class="px-4 py-2">
                                        {{ \Carbon\Carbon::parse($election->end_date)->format('M d, Y h:i A') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-gray-500">No active elections found.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Closed Elections Modal --}}
    <div id="closedModal" class="hidden fixed inset-0 bg-black/50 z-50">
        <div class="flex items-center justify-center w-full h-full">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-3xl p-6 max-h-[80vh] overflow-y-auto relative">
                <button onclick="closeModal('closedModal')"
                    class="sticky top-0 right-0 float-right bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg z-50 ml-auto mb-4">
                    ✖
                </button>

                <h2 class="text-xl font-bold mb-4 text-red-600">🔴 Closed Elections</h2>
                @if ($closedList->isNotEmpty())
                    <table class="min-w-full border border-gray-200 rounded-lg">
                        <thead class="bg-gray-100 text-gray-700">
                            <tr>
                                <th class="px-4 py-2 text-left">Title</th>
                                <th class="px-4 py-2 text-left">Start Date</th>
                                <th class="px-4 py-2 text-left">End Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($closedList as $election)
                                <tr class="border-t hover:bg-gray-50">
                                    <td class="px-4 py-2">{{ $election->title }}</td>
                                    <td class="px-4 py-2">
                                        {{ \Carbon\Carbon::parse($election->start_date)->format('M d, Y h:i A') }}</td>
                                    <td class="px-4 py-2">
                                        {{ \Carbon\Carbon::parse($election->end_date)->format('M d, Y h:i A') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-gray-500">No closed elections found.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Upcoming Elections Modal --}}
    <div id="upcomingModal" class="hidden fixed inset-0 bg-black/50 z-50">
        <div class="flex items-center justify-center w-full h-full">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-3xl p-6 max-h-[80vh] overflow-y-auto relative">
                <button onclick="closeModal('upcomingModal')"
                    class="sticky top-0 right-0 float-right bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg z-50 ml-auto mb-4">
                    ✖
                </button>

                <h2 class="text-xl font-bold mb-4 text-green-600">🕒 Upcoming Elections</h2>
                @if ($upcomingList->isNotEmpty())
                    <table class="min-w-full border border-gray-200 rounded-lg">
                        <thead class="bg-gray-100 text-gray-700">
                            <tr>
                                <th class="px-4 py-2 text-left">Title</th>
                                <th class="px-4 py-2 text-left">Start Date</th>
                                <th class="px-4 py-2 text-left">End Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($upcomingList as $election)
                                <tr class="border-t hover:bg-gray-50">
                                    <td class="px-4 py-2">{{ $election->title }}</td>
                                    <td class="px-4 py-2">
                                        {{ \Carbon\Carbon::parse($election->start_date)->format('M d, Y h:i A') }}</td>
                                    <td class="px-4 py-2">
                                        {{ \Carbon\Carbon::parse($election->end_date)->format('M d, Y h:i A') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-gray-500">No upcoming elections found.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Voters Modal --}}
    <div id="votersModal" class="hidden fixed inset-0 bg-black/50 z-50">
        <div class="flex items-center justify-center w-full h-full">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-3xl p-6 max-h-[80vh] overflow-y-auto relative">
                <button onclick="closeModal('votersModal')"
                    class="sticky top-0 right-0 float-right bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg z-50 ml-auto mb-4">
                    ✖
                </button>

                <h2 class="text-xl font-bold mb-4 text-yellow-600">🗳️ Registered Voters</h2>
                @if ($voters->isNotEmpty())
                    <table class="min-w-full border border-gray-200 rounded-lg">
                        <thead class="bg-gray-100 text-gray-700 sticky top-0">
                            <tr>
                                <th class="px-4 py-2 text-left">Voter ID</th>
                                <th class="px-4 py-2 text-left">Name</th>
                                <th class="px-4 py-2 text-left">Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($voters as $voter)
                                <tr class="border-t hover:bg-gray-50">
                                    <td class="px-4 py-2">{{ $voter->voter_id }}</td>
                                    <td class="px-4 py-2">{{ $voter->name }}</td>
                                    <td class="px-4 py-2">{{ $voter->email }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-gray-500">No voters registered.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Chart Script --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('votesChart').getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(9, 24, 45, 0.4)');
        gradient.addColorStop(1, 'rgba(9, 24, 45, 0)');

        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($chartLabels),
                datasets: [{
                    label: 'Votes',
                    data: @json($chartVotes),
                    borderColor: '#09182D',
                    backgroundColor: gradient,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#09182D',
                    pointBorderColor: '#09182D',
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    pointHoverBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#e5e7eb',
                            drawBorder: false
                        },
                        ticks: {
                            stepSize: 1
                        }
                    },
                    x: {
                        grid: {
                            color: '#e5e7eb',
                            drawBorder: false
                        },
                        ticks: {
                            color: '#374151',
                            font: {
                                weight: 500
                            }
                        }
                    }
                },
                onClick: (e) => {
                    const points = chart.getElementsAtEventForMode(e, 'nearest', {
                        intersect: true
                    }, true);
                    if (points.length) {
                        const index = points[0].index;
                        const label = chart.data.labels[index];
                        const votes = chart.data.datasets[0].data[index];
                        showChartModal(label, votes);
                    }
                }
            }
        });

        function showChartModal(title, votes) {
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-black/50 flex items-center justify-center z-50';
            modal.innerHTML = `
                    <div class="bg-white p-6 rounded-xl shadow-xl w-full max-w-md text-center">
                        <h2 class="text-xl font-bold mb-3 text-gray-800">${title}</h2>
                        <p class="text-lg text-gray-600 mb-4">Total Votes: <span class="font-bold">${votes}</span></p>
                        <button onclick="this.parentElement.parentElement.remove()" class="bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded-lg">Close</button>
                    </div>
                `;
            document.body.appendChild(modal);
        }

        function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
        }

        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
        }
    </script>
@endsection
