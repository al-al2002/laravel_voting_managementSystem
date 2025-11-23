@extends('layouts.admin')

@section('title', 'Manage Voters')

@section('content')
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-2xl font-semibold text-gray-800 mb-6">Registered Voters</h2>

        {{-- Filter --}}
        <div class="mb-6 flex items-center gap-2">
            <form method="GET" action="{{ route('admin.voters.index') }}">
                <select name="filter" onchange="this.form.submit()"
                    class="border border-gray-300 rounded-md px-4 py-2 text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#09182D] focus:border-[#09182D]">
                    <option value="">All Status</option>
                    <option value="eligible" {{ request('filter') == 'eligible' ? 'selected' : '' }}>Eligible</option>
                    <option value="not_eligible" {{ request('filter') == 'not_eligible' ? 'selected' : '' }}>Not Eligible
                    </option>
                </select>
            </form>
        </div>

        @if ($voters->count() > 0)
            <div class="overflow-x-auto">
                <div class="min-w-[800px]"> {{-- prevents table from stretching too wide --}}
                    <table class="w-full divide-y divide-gray-200 border rounded-lg table-auto">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Voter ID</th>
                                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Name</th>
                                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Email</th>
                                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Created At</th>
                                <th class="px-4 py-2 text-center text-sm font-semibold text-gray-700">Skipped Elections</th>
                                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Eligibility</th>
                                <th class="px-4 py-2 text-center text-sm font-semibold text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($voters as $voter)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2">{{ $voter->voter_id }}</td>
                                    <td class="px-4 py-2 flex items-center gap-3">
                                        @if ($voter->profile_photo)
                                            <img src="{{ $voter->profile_photo_url ?? asset('images/default-avatar.png') }}"
                                                alt="{{ $voter->name }}'s photo"
                                                class="w-10 h-10 rounded-full object-cover border border-gray-300">
                                        @else
                                            <div
                                                class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 text-sm">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M5.121 17.804A9.004 9.004 0 0112 15c2.21 0 4.21.804 5.879 2.14M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                            </div>
                                        @endif
                                        <span class="font-medium text-gray-800">{{ $voter->name }}</span>
                                    </td>
                                    <td class="px-4 py-2 text-gray-600">{{ $voter->email }}</td>
                                    <td class="px-4 py-2 text-gray-600">{{ $voter->created_at->format('M d, Y h:i A') }}
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        <button type="button"
                                            class="skipped-btn px-3 py-1 rounded-lg bg-gray-100 text-blue-600 hover:bg-blue-200 font-medium text-sm"
                                            data-elections='@json($voter->skippedElectionsWithId())'>
                                            {{ $voter->skippedElectionsCount() }}
                                        </button>
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        @if ($voter->finalEligibility())
                                            <span
                                                class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">
                                                Eligible
                                                @if ($voter->eligibility_overridden)
                                                    <span class="text-xs" title="Admin Override">⚠️</span>
                                                @endif
                                            </span>
                                        @else
                                            <span
                                                class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700">
                                                Not Eligible
                                                @if ($voter->eligibility_overridden)
                                                    <span class="text-xs" title="Admin Override">⚠️</span>
                                                @endif
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        <form action="{{ route('admin.voters.toggle', $voter->id) }}" method="POST"
                                            class="inline toggle-form">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="px-3 py-1 text-sm rounded-lg {{ $voter->finalEligibility() ? 'bg-red-500 text-white hover:bg-red-600' : 'bg-green-500 text-white hover:bg-green-600' }}">
                                                {{ $voter->finalEligibility() ? 'Mark Not Eligible' : 'Mark Eligible' }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Pagination --}}
            <div class="mt-4">{{ $voters->appends(request()->query())->links() }}</div>
        @else
            <div class="mt-6 p-4 bg-yellow-50 border-l-4 border-yellow-400 text-yellow-800 rounded">
                <p class="text-sm">No voters found matching the selected filter.</p>
            </div>
        @endif
    </div>

    {{-- SweetAlert --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Skipped elections popup
            document.querySelectorAll('.skipped-btn').forEach(button => {
                button.addEventListener('click', function() {
                    let elections = JSON.parse(this.dataset.elections);

                    if (elections.length > 0) {
                        Swal.fire({
                            title: 'Skipped Elections',
                            html: `
        <div style="max-height: 300px; overflow-y: auto; text-align: left; padding-right: 5px;">
            <ul class="space-y-2">
                ${elections.map(e => `
                                            <li class="p-2 bg-gray-50 rounded border">
                                                <a href="/admin/elections/${e.id}" class="text-blue-600 hover:underline">${e.title}</a>
                                            </li>
                                        `).join('')}
            </ul>
        </div>
    `,
                            icon: 'info',
                            confirmButtonText: 'Close',
                            confirmButtonColor: '#09182D',
                            width: 500
                        });

                    } else {
                        Swal.fire({
                            title: 'No Skipped Elections',
                            text: "This voter has not skipped any elections.",
                            icon: 'success',
                            confirmButtonText: 'Close',
                            confirmButtonColor: '#09182D',
                        });
                    }
                });
            });

            // Toggle confirmation
            document.querySelectorAll('.toggle-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "This will update the voter's eligibility status.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, update it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });

            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: "{{ session('success') }}",
                    timer: 1200,
                    showConfirmButton: false,
                    position: 'top-end',
                    toast: true
                });
            @endif
        });
    </script>
@endsection
