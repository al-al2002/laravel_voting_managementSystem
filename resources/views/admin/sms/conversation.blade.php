@extends('layouts.admin')

@section('title', 'Conversation')

@section('content')
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        {{-- Header --}}
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-gray-800">💬 Conversation</h2>
            <a href="{{ route('admin.sms.index') }}"
                class="bg-[#09182D] hover:bg-[#0c223f] text-white px-4 py-2 rounded-lg transition">
                ← Back to Inbox
            </a>
        </div>

        {{-- Chat area --}}
        <div id="chatBox" class="space-y-4 mb-6 max-h-[400px] overflow-y-auto p-3 bg-gray-50 rounded-lg">
            @foreach ($messages as $message)
                <div
                    class="p-3 rounded-lg w-fit max-w-[75%]
                            {{ $message->sender_type === 'admin' ? 'bg-blue-100 ml-auto text-right' : 'bg-gray-100' }}">
                    <strong>{{ $message->sender_type === 'admin' ? 'Admin' : $message->user->name }}</strong>:

                    {{-- Message text --}}
                    @if (!empty($message->message))
                        <p class="mt-1 text-gray-800">{{ $message->message }}</p>
                    @endif

                    {{-- Attached images --}}
                    @if ($message->image)
                        <div class="mt-2 grid grid-cols-2 gap-2">
                            @foreach ($message->image_urls as $imgUrl)
                                <img src="{{ $imgUrl }}" alt="Image"
                                    class="rounded-lg border border-gray-300 object-cover w-full h-40 cursor-pointer"
                                    onclick="window.open('{{ $imgUrl }}', '_blank')">
                            @endforeach
                        </div>
                    @endif

                    {{-- Formatted Time --}}
                    <small class="text-gray-500 block mt-1">{{ $message->created_at->format('d M Y h:i A') }}</small>
                </div>
            @endforeach
        </div>

        {{-- Send reply form --}}
        <form action="{{ route('admin.sms.reply', $conversation_id) }}" method="POST" enctype="multipart/form-data"
            id="replyForm">
            @csrf
            <textarea name="reply" rows="3" class="w-full border rounded-lg p-2 mb-4" placeholder="Write your reply..."></textarea>

            {{-- Image preview --}}
            <div id="imagePreview" class="flex flex-wrap gap-3 mb-4"></div>

            <input type="file" name="image[]" id="imageInput" multiple class="mb-4 block text-sm text-gray-600">

            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 rounded bg-[#09182D] text-white hover:bg-[#0c223f] transition">
                    Send Reply
                </button>
            </div>
        </form>
    </div>

    {{-- Scripts --}}
    <script>
        const chatBox = document.getElementById('chatBox');
        const imageInput = document.getElementById('imageInput');
        const imagePreview = document.getElementById('imagePreview');
        const replyForm = document.getElementById('replyForm');

        // Auto scroll to bottom on load (show latest messages)
        chatBox.scrollTop = chatBox.scrollHeight;

        // Image preview before sending
        imageInput.addEventListener('change', function() {
            imagePreview.innerHTML = '';
            Array.from(this.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = e => {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = "w-24 h-24 object-cover rounded border border-gray-300";
                    imagePreview.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        });

        // Keep scroll position at bottom after sending a reply
        replyForm.addEventListener('submit', () => {
            localStorage.setItem('scrollPosition', chatBox.scrollHeight);
        });

        // Restore scroll position after page reload
        window.addEventListener('load', () => {
            const pos = localStorage.getItem('scrollPosition');
            if (pos) {
                chatBox.scrollTop = pos;
                localStorage.removeItem('scrollPosition');
            } else {
                chatBox.scrollTop = chatBox.scrollHeight;
            }
        });
    </script>
@endsection
