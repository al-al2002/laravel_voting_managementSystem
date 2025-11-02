@extends('layouts.user')

@section('title', 'Inbox')

@section('content')
    {{-- This fragment is injected into the layout's floating inbox container via AJAX.
         It should NOT include the page wrapper or inline scripts. --}}

    <div id="inboxFragment">
        {{-- Header --}}
        <div
            class="flex justify-between items-center px-4 py-3 border-b border-gray-600 bg-gradient-to-r from-[#11224080] to-[#0c1a3280] backdrop-blur-md">
            <div class="flex items-center space-x-2">
                <span class="text-xl">📥</span>
                <h2 class="text-lg font-semibold">Inbox</h2>
            </div>
            <div class="flex gap-2">
                <button id="newMessageBtn"
                    class="bg-yellow-500 hover:bg-yellow-600 text-yellow-900 px-3 py-1 rounded-lg text-sm font-semibold transition">
                    + New
                </button>
                <button id="closeInboxBtn"
                    class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-lg text-sm font-semibold transition">
                    ✖ Close
                </button>
            </div>
        </div>

        {{-- Content Area --}}
        <div id="inboxList" class="p-2">
            @if ($messages->isEmpty())
                <div class="p-6 text-center text-gray-400">No conversations found.</div>
            @else
                <div class="divide-y divide-gray-700">
                    @foreach ($messages as $conv)
                        <a href="{{ url('/user/sms/conversation') }}/{{ $conv->conversation_id }}"
                            class="openConversation px-4 py-3 hover:bg-[#0e2a48] transition flex items-start gap-3"
                            data-conversation-id="{{ $conv->conversation_id }}">
                            <div class="flex-1 text-left">
                                <div class="flex items-center justify-between">
                                    <div class="text-sm text-gray-100 font-semibold truncate">
                                        {{ Str::limit($conv->latest_message, 70) }}</div>
                                    <div class="text-xs text-gray-400">{{ optional($conv->latest_time)->diffForHumans() }}
                                    </div>
                                </div>
                                <div class="text-xs text-gray-400 mt-1 truncate">
                                    {{ $conv->sender_type === 'admin' ? 'Admin' : 'You' }}</div>
                            </div>
                            @if (!empty($conv->unread_count_admin) && $conv->unread_count_admin > 0)
                                <div class="flex items-center">
                                    <span
                                        class="bg-red-600 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $conv->unread_count_admin }}</span>
                                </div>
                            @endif
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
