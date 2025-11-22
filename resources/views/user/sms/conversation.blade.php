@extends('layouts.user')

@section('title', 'Conversation')

@section('content')
    {{-- Conversation can be rendered as an AJAX fragment (injected into the top-right inbox)
         or as a small floating panel when visited directly. --}}

    @if (request()->ajax())
        <div id="inboxFragment">
        @else
            <div class="fixed top-16 right-6 w-96 inbox-bg rounded-xl overflow-hidden z-50 transition-all duration-200">
                <div id="inboxContent" class="max-h-[80vh] overflow-y-auto">
                    <div id="inboxFragment">
    @endif

    {{-- Header --}}
    <div class="flex justify-between items-center p-4 border-b border-gray-600">
        <h2 class="text-lg font-semibold flex items-center gap-2">💬 Conversation with Admin</h2>
        <div class="flex gap-2">
            @if (request()->ajax())
                <button id="closeInboxBtn" class="bg-red-500 px-3 py-1 rounded-lg text-sm hover:bg-gray-600 transition">✖
                    Close</button>
            @else
                <a href="{{ route('user.messages.index') }}"
                    class="bg-red-500 px-3 py-1 rounded-lg text-sm hover:bg-gray-600 transition">✖ Close</a>
            @endif
        </div>
    </div>

    {{-- Messages --}}
    <div id="messagesContainer" class="flex-1 px-4 py-3 space-y-4 overflow-y-auto">
        @forelse ($messages as $msg)
            <div class="flex {{ $msg->sender_type === 'user' ? 'justify-end' : 'justify-start' }} animate-fade-in">
                <div
                    class="{{ $msg->sender_type === 'user' ? 'bg-blue-600' : 'bg-gray-700' }} px-4 py-2 max-w-[80%] flex flex-col gap-1 rounded-lg relative">
                    @if ($msg->message)
                        <p>{{ $msg->message }}</p>
                    @endif
                    @if (!empty($msg->image_urls))
                        <div class="flex flex-wrap gap-2 mt-1">
                            @foreach ($msg->image_urls as $imgUrl)
                                <img src="{{ $imgUrl }}" class="rounded-lg max-w-full">
                            @endforeach
                        </div>
                    @endif
                    <span class="text-gray-300 text-xs self-end">{{ $msg->created_at->format('h:i A') }}</span>
                </div>
            </div>
        @empty
            <p class="text-center text-gray-400 mt-4">No messages yet.</p>
        @endforelse
    </div>

    {{-- Preview & Reply --}}
    <div id="previewContainer" class="flex gap-2 px-4 py-2 overflow-x-auto"></div>

    <form id="replyForm" enctype="multipart/form-data"
        class="flex items-center gap-2 p-4 border-t border-gray-600 bg-[#1E293B]"
        action="{{ route('user.messages.reply', $conversation_id) }}" method="POST">
        @csrf
        <input type="text" name="message" placeholder="Type a message..."
            class="flex-1 bg-gray-800 border border-gray-700 rounded-full px-4 py-2 text-sm text-white placeholder-gray-400 focus:outline-none focus:ring focus:ring-blue-500">
        <label for="image" class="cursor-pointer text-gray-300 hover:text-white transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V7M16 3.5a2.5 2.5 0 115 0 2.5 2.5 0 01-5 0zM4 13l4-4 3 3 5-5 4 4" />
            </svg>
        </label>
        <input type="file" name="image[]" id="image" multiple class="hidden">
        <button type="submit"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-full text-sm transition flex items-center gap-1">Send</button>
    </form>

    @if (request()->ajax())
        </div>
    @else
        </div>
        </div>
        </div>
    @endif

    <style>
        @keyframes fade-in {
            0% {
                opacity: 0;
                transform: translateY(10px);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fade-in 0.25s ease-out;
        }
    </style>

    <script>
        // minimal client-side behavior for direct page loads (not needed when loaded via AJAX)
        const messagesContainer = document.getElementById('messagesContainer');
        window.addEventListener('load', () => {
            if (messagesContainer) messagesContainer.scrollTop = messagesContainer.scrollHeight;
        });
    </script>

@endsection
