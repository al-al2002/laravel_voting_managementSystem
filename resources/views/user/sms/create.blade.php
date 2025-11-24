@extends('layouts.user')

@section('title', 'New Message')

@section('content')
    {{-- This view can be returned as an AJAX fragment (injected into the top-right dropdown)
         or rendered directly as a small floating panel when visited as a full page. --}}

    @if (request()->ajax())
        <div id="inboxFragment">
        @else
            <div class="fixed top-16 right-6 w-96 inbox-bg rounded-xl overflow-hidden z-50 transition-all duration-200">
                <div id="inboxContent">
                    <div id="inboxFragment">
    @endif
    {{-- Header --}}
    <div
        class="flex justify-between items-center px-4 py-3 border-b border-gray-600 bg-gradient-to-r from-[#11224080] to-[#0c1a3280] backdrop-blur-md">
        <div class="flex items-center space-x-2">
            <span class="text-xl">✉️</span>
            <h2 class="text-lg font-semibold">Chat with Admin</h2>
        </div>
        <div class="flex gap-2">
            @if (request()->ajax())
                <button id="closeInboxBtn"
                    class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-lg text-sm font-semibold transition">✖
                    Close</button>
            @else
                <a href="{{ route('user.messages.index') }}"
                    class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-lg text-sm font-semibold transition">✖
                    Close</a>
            @endif
        </div>
    </div>

    {{-- Info Text --}}
    <div class="text-center text-gray-300 p-4">
        @if ($conversationId)
            Continue your conversation with Admin.
        @else
            Start a new conversation with Admin.
        @endif
    </div>

    {{-- Conversation / Form --}}
    <div class="p-4">
        @if ($conversationId)
            <a href="{{ route('user.messages.conversation', $conversationId) }}"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition">Open
                Conversation</a>
        @else
            <form action="{{ route('user.messages.store') }}" method="POST" enctype="multipart/form-data"
                class="flex flex-col gap-3" id="createMessageForm">
                @csrf

                {{-- File input and previews appear above the message box so user sees attachments first --}}
                <div class="flex items-start gap-2">
                    <input type="file" name="image[]" multiple class="text-sm text-gray-300">
                </div>

                {{-- Preview selected images before sending (displayed above the message textarea) --}}
                <div id="imagePreview" class="mt-3 mb-3 flex flex-wrap gap-2"></div>

                <textarea name="message" rows="4"
                    class="w-full bg-gray-800/80 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:ring focus:ring-blue-500"
                    placeholder="Write your message..." required></textarea>

                <div class="flex justify-end">
                    <button type="submit" id="sendMessageBtn"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition">
                        <span id="sendMessageText">Send</span>
                        <span id="sendMessageSpinner" class="hidden">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Sending...
                        </span>
                    </button>
                </div>
            </form>

            <script>
                // Loading state for create message form
                document.getElementById('createMessageForm').addEventListener('submit', function() {
                    const btn = document.getElementById('sendMessageBtn');
                    const btnText = document.getElementById('sendMessageText');
                    const btnSpinner = document.getElementById('sendMessageSpinner');
                    btn.disabled = true;
                    btn.classList.add('opacity-50', 'cursor-not-allowed');
                    btnText.classList.add('hidden');
                    btnSpinner.classList.remove('hidden');
                });
            </script>
        @endif
    </div>
    @if (request()->ajax())
        </div>
    @else
        </div>
        </div>
        </div>
    @endif
@endsection
