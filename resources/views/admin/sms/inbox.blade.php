@extends('layouts.admin')

@section('title', 'Admin Inbox')

@section('content')
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 relative">

        {{-- Close Button --}}
        <a href="{{ route('admin.dashboard') }}"
            class="absolute top-4 right-4 bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-lg text-sm font-semibold transition">
            ✖ Close
        </a>

        <h2 class="text-2xl font-semibold text-gray-800 mb-6">📥 Inbox (Messages from Voters)</h2>

        {{-- Success SweetAlert --}}
        @if (session('success'))
            <script>
                document.addEventListener("DOMContentLoaded", () => {
                    Swal.fire({
                        icon: 'success',
                        title: '{{ session('success') }}',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 1500,
                        timerProgressBar: true,
                        width: 250,
                        padding: '0.5rem',
                    });
                });
            </script>
        @endif

        @if ($messages->count())
            <div class="overflow-x-auto rounded-lg border">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Sender</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Message</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Status</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Time Sent</th>
                            <th class="px-4 py-2 text-center text-sm font-semibold text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($messages as $message)
                            @php
                                $unreadCount = \App\Models\Message::where('conversation_id', $message->conversation_id)
                                    ->where('status', 'unread')
                                    ->where('sender_type', 'user')
                                    ->count();
                                $images = $message->image ? json_decode($message->image, true) : [];
                                $lastMessage = \App\Models\Message::where('conversation_id', $message->conversation_id)
                                    ->latest('created_at')
                                    ->first();
                                $lastSender = $lastMessage->sender_type === 'admin' ? 'You' : $message->user->name;
                            @endphp

                            <tr class="{{ $unreadCount > 0 ? 'bg-yellow-50' : '' }}">
                                {{-- Sender --}}
                                <td class="px-4 py-2 flex items-center gap-3">
                                    @if ($message->user->profile_photo)
                                        <img src="{{ $message->user->profile_photo_url ?? asset('images/default-avatar.png') }}"
                                            alt="Voter Picture" class="w-12 h-12 rounded-full object-cover">
                                    @else
                                        <div
                                            class="w-12 h-12 rounded-full bg-gray-300 flex items-center justify-center text-gray-600 font-bold">
                                            {{ strtoupper(substr($message->user->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div class="flex flex-col">
                                        <span class="font-semibold">{{ $message->user->name }}</span>
                                        <small class="text-gray-500">ID: {{ $message->user->voter_id }}</small>
                                    </div>
                                    @if ($unreadCount > 0)
                                        <span class="ml-2 bg-red-500 text-white text-xs font-bold rounded-full px-2 py-0.5">
                                            {{ $unreadCount }}
                                        </span>
                                    @endif
                                </td>

                                {{-- Message preview --}}
                                <td class="px-4 py-2 text-gray-600">
                                    <div>{{ Str::limit($message->message, 50) }}</div>

                                    @if (!empty($message->image_urls))
                                        <div class="flex flex-wrap gap-2 mt-1">
                                            @foreach ($message->image_urls as $imgUrl)
                                                <img src="{{ $imgUrl }}" class="w-16 h-16 object-cover rounded-lg">
                                            @endforeach
                                        </div>
                                    @endif

                                    <small class="text-gray-400 block mt-1 text-xs">
                                        Last message by: {{ $lastSender }}
                                    </small>
                                </td>

                                {{-- Status --}}
                                <td class="px-4 py-2">
                                    @if ($unreadCount > 0)
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700">
                                            Unread ({{ $unreadCount }})
                                        </span>
                                    @else
                                        <span
                                            class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">
                                            Read
                                        </span>
                                    @endif
                                </td>

                                {{-- Time Sent --}}
                                <td class="px-4 py-2 text-gray-500 text-sm">
                                    {{ $message->created_at->format('d M Y h:i A') }}
                                </td>

                                {{-- Actions --}}
                                <td class="px-4 py-2 text-center flex justify-center gap-2">
                                    <a href="{{ route('admin.sms.conversation', $message->conversation_id) }}"
                                        class="relative inline-block px-3 py-1 text-sm rounded-lg bg-[#09182D] text-white hover:bg-[#12263f]">
                                        💬 View
                                    </a>
                                    <form action="{{ route('admin.sms.destroyConversation', $message->conversation_id) }}"
                                        method="POST" class="delete-conversation">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-block px-3 py-1 text-sm rounded-lg bg-red-600 text-white hover:bg-red-700 transition">
                                            🗑 Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="mt-6 p-4 bg-yellow-50 border-l-4 border-yellow-400 text-yellow-800 rounded">
                <p class="text-sm">No messages received yet.</p>
            </div>
        @endif
    </div>

    {{-- SweetAlert Delete --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.body.addEventListener('submit', function(e) {
                const form = e.target;
                if (form.classList.contains('delete-conversation')) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "This will permanently delete the conversation!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                }
            });
        });
    </script>
@endsection
