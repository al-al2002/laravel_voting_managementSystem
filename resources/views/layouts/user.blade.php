<!DOCTYPE html>
<html lang="en">

@php
    use Illuminate\Support\Facades\Auth;
    use App\Models\Message;

    $user = Auth::user();

    // Only count unread messages that are not deleted by the user.
    // If user is not authenticated (guest), default to zero and avoid accessing properties on null.
    $unreadCount = 0;
    if ($user) {
        $unreadCount = Message::where('user_id', $user->id)
            ->where('to', 'user')
            ->where('status', 'unread')
            ->where('deleted_by_user', false)
            ->count();
    }
@endphp


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'VoteMaster - Voter Dashboard')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/votemaster.png') }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
            background-color: #09182D;
        }

        .card {
            background: #10243F;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 20px;
            transition: transform .2s, box-shadow .2s;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, .4);
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 8px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
        }
    </style>
</head>


<body class="text-white">

    {{-- ✅ Skip header if the request is via AJAX (fetch) --}}
    @if (!request()->ajax())
        <header class="bg-[#09182D] shadow-md border-b border-gray-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 flex items-center justify-between">

                {{-- Logo --}}
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 rounded-lg overflow-hidden flex items-center justify-center">
                        <img src="{{ asset('images/votemaster.png') }}" alt="VoteMaster Logo"
                            class="w-full h-full object-contain">
                    </div>
                    <div>
                        <h1 class="text-yellow-400 font-bold">VoteMaster</h1>
                        <p class="text-gray-400 text-sm">Your Voice, Your Vote</p>
                    </div>
                </div>

                {{-- Right Section --}}
                <div class="flex items-center space-x-4">
                    <a href="{{ route('user.results.index') }}"
                        class="bg-yellow-400 hover:bg-yellow-500 text-black px-4 py-2 rounded-lg font-semibold transition">
                        Results
                    </a>

                    <a href="{{ route('user.live-monitor.index') }}"
                        class="bg-yellow-400 hover:bg-yellow-500 text-black px-4 py-2 rounded-lg font-semibold transition">
                        Live Monitor
                    </a>

                    {{-- 📥 Inbox Button with Unread Badge --}}
                    <button id="openInboxBtn"
                        class="relative px-4 py-2 rounded-lg font-semibold bg-yellow-400 hover:bg-yellow-500 text-black transition">
                        📩 Inbox
                        @if ($unreadCount > 0)
                            <span id="inboxUnreadBadge"
                                class="absolute -top-1 -right-1 bg-red-600 text-white text-xs font-bold px-2 py-0.5 rounded-full">
                                {{ $unreadCount }}
                            </span>
                        @else
                            <span id="inboxUnreadBadge"
                                class="hidden absolute -top-1 -right-1 bg-red-600 text-white text-xs font-bold px-2 py-0.5 rounded-full">0</span>
                        @endif
                    </button>

                    {{-- User Dropdown --}}
                    <div class="relative">
                        <button id="userMenuBtn" class="flex items-center space-x-2 focus:outline-none">
                            <div class="w-10 h-10 rounded-full overflow-hidden border-2 border-yellow-400">
                                @if ($user && $user->profile_photo)
                                    <img src="{{ asset('storage/' . $user->profile_photo) }}" alt="Profile"
                                        class="w-full h-full object-cover">
                                @else
                                    <div
                                        class="w-full h-full flex items-center justify-center bg-gray-600 text-white text-lg font-semibold">
                                        {{ $user ? strtoupper(substr($user->name, 0, 1)) : 'G' }}
                                    </div>
                                @endif
                            </div>
                            <div class="hidden sm:block text-left">
                                <span class="font-semibold block">{{ $user ? $user->name : 'Guest' }}</span>
                                <span class="text-xs text-gray-400">ID: {{ $user ? $user->voter_id : 'N/A' }}</span>
                            </div>
                            <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        {{-- Dropdown menu --}}
                        <div id="userMenu"
                            class="absolute right-0 mt-2 w-48 bg-[#10243F] border border-gray-700 rounded-lg shadow-lg hidden z-50">
                            <a href="{{ route('user.profile.edit') }}"
                                class="block px-4 py-2 text-sm text-white hover:bg-gray-700">Edit Profile</a>
                            <a href="{{ route('user.profile.settings') }}"
                                class="block px-4 py-2 text-sm text-white hover:bg-gray-700">Settings</a>
                            <a href="{{ route('user.password.change') }}"
                                class="block px-4 py-2 text-sm text-white hover:bg-gray-700">Change Password</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="w-full text-left block px-4 py-2 text-sm text-red-400 hover:bg-gray-700">
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>
    @endif

    {{-- =================== User Menu Toggle =================== --}}
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const btn = document.getElementById("userMenuBtn");
            const menu = document.getElementById("userMenu");

            if (btn && menu) {
                btn.addEventListener("click", (e) => {
                    e.stopPropagation();
                    menu.classList.toggle("hidden");
                });

                document.addEventListener("click", (e) => {
                    if (!btn.contains(e.target) && !menu.contains(e.target)) {
                        menu.classList.add("hidden");
                    }
                });
            }
        });
    </script>
    {{-- =================== 📥 Inbox Modal =================== --}}


    @if (!request()->ajax())
        <style>
            /* === Dropdown Animations === */
            @keyframes slideDown {
                from {
                    opacity: 0;
                    transform: translateY(-10px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            @keyframes slideUp {
                from {
                    opacity: 1;
                    transform: translateY(0);
                }

                to {
                    opacity: 0;
                    transform: translateY(-10px);
                }
            }

            .animate-slideDown {
                animation: slideDown 0.25s ease-out forwards;
            }

            .animate-slideUp {
                animation: slideUp 0.25s ease-in forwards;
            }

            /* === Cool Glass Gradient Background === */
            .inbox-bg {
                background: linear-gradient(145deg, rgba(17, 34, 64, 0.95), rgba(12, 26, 50, 0.98));
                backdrop-filter: blur(12px);
                border: 1px solid rgba(255, 255, 255, 0.1);
                box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
            }

            /* Loader overlay inside the inbox */
            #inboxLoader {
                position: absolute;
                inset: 0;
                display: flex;
                align-items: center;
                justify-content: center;
                background: rgba(2, 6, 23, 0.45);
                z-index: 50;
            }

            .loader {
                border: 4px solid rgba(255, 255, 255, 0.12);
                border-top-color: rgba(255, 255, 255, 0.95);
                border-radius: 9999px;
                width: 36px;
                height: 36px;
                animation: spin 1s linear infinite;
            }

            @keyframes spin {
                to {
                    transform: rotate(360deg);
                }
            }
        </style>

        <!-- Floating Inbox Only -->
        <div id="inboxDropdown"
            class="hidden absolute top-16 right-6 w-96 inbox-bg rounded-xl overflow-hidden z-50 transition-all duration-200 h-[70vh]">
            <div id="inboxContent" class="flex flex-col h-full custom-scrollbar relative">
                <div id="inboxLoader" class="hidden" aria-hidden="true">
                    <div class="loader" aria-hidden="true"></div>
                </div>
                <div id="inboxInitial" class="w-full text-center text-gray-400 py-6">Loading inbox...</div>
            </div>
        </div>

        <script>
            const openInboxBtn = document.getElementById('openInboxBtn');
            const inboxDropdown = document.getElementById('inboxDropdown');
            const inboxContent = document.getElementById('inboxContent');

            // Update unread badge by calling the server endpoint
            async function updateUnreadBadge() {
                try {
                    const resp = await fetch('{{ route('user.unread.count') }}', {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    if (!resp.ok) return;
                    const data = await resp.json();
                    const badge = document.getElementById('inboxUnreadBadge');
                    if (!badge) return;
                    const count = parseInt(data.count || 0, 10);
                    if (count > 0) {
                        badge.textContent = count;
                        badge.classList.remove('hidden');
                    } else {
                        badge.textContent = 0;
                        badge.classList.add('hidden');
                    }
                } catch (err) {
                    console.debug('Failed to update unread badge', err);
                }
            }

            // Scroll messages container to bottom if present
            function inboxScrollToBottom() {
                try {
                    const mc = document.getElementById('messagesContainer');
                    if (!mc) return;
                    mc.scrollTop = mc.scrollHeight;
                } catch (err) {
                    console.debug('Failed to scroll to bottom', err);
                }
            }

            // Show/hide the inbox loader overlay
            function showInboxLoader() {
                try {
                    const loader = document.getElementById('inboxLoader');
                    if (!loader) return;
                    loader.classList.remove('hidden');
                } catch (err) {
                    console.debug('showInboxLoader failed', err);
                }
            }

            function hideInboxLoader() {
                try {
                    const loader = document.getElementById('inboxLoader');
                    if (!loader) return;
                    loader.classList.add('hidden');
                } catch (err) {
                    console.debug('hideInboxLoader failed', err);
                }
            }

            // Show a small validation state on the message input (red border only)
            function showFormError(form, text) {
                try {
                    if (!form) return;
                    // Clear previous visual state first
                    clearFormError(form);

                    // Red border on the message input (textarea or input)
                    const messageNode = form.querySelector('textarea[name="message"]') || form.querySelector(
                        'input[name="message"]');
                    if (messageNode) {
                        // Add classes to indicate error (Tailwind helpers)
                        messageNode.classList.add('border-red-500', 'ring-2', 'ring-red-500');
                        // Store a flag so clearFormError knows to remove these
                        messageNode.dataset.hasValidationBorder = '1';
                    }

                    // Auto-clear the red border after 3 seconds
                    try {
                        const tid = setTimeout(() => {
                            try {
                                clearFormError(form);
                            } catch (e) {
                                console.debug('error clearing validation', e);
                            }
                        }, 3000);
                        form._validationTimeout = tid;
                    } catch (e) {
                        console.debug('failed to schedule validation clear', e);
                    }
                } catch (err) {
                    console.debug('showFormError failed', err);
                }
            }

            function clearFormError(form) {
                try {
                    if (!form) return;
                    const existing = form.querySelector('.form-error');
                    if (existing && existing.parentNode) existing.parentNode.removeChild(existing);

                    // Remove validation border on the message input if we added it
                    const messageNode = form.querySelector('textarea[name="message"]') || form.querySelector(
                        'input[name="message"]');
                    if (messageNode && messageNode.dataset.hasValidationBorder) {
                        messageNode.classList.remove('border-red-500', 'ring-2', 'ring-red-500');
                        delete messageNode.dataset.hasValidationBorder;
                    }

                    // Remove transient toast if present
                    try {
                        // Cancel timeout if pending
                        if (form._validationTimeout) {
                            clearTimeout(form._validationTimeout);
                            try {
                                delete form._validationTimeout;
                            } catch (e) {}
                        }
                        const toast = (inboxContent && inboxContent.querySelector('.inbox-validation-toast')) || document
                            .querySelector('.inbox-validation-toast');
                        if (toast && toast.parentNode) toast.parentNode.removeChild(toast);
                    } catch (e) {
                        console.debug('failed to remove toast', e);
                    }
                } catch (err) {
                    console.debug('clearFormError failed', err);
                }
            }

            // Load a conversation fragment into the inbox dropdown and ensure messages are present.
            // If the injected fragment shows no messages (race), retry once after a short delay.
            async function loadConversationIntoInbox(convId) {
                if (!convId) return;
                // keep existing content visible; show overlay loader
                showInboxLoader();
                try {
                    const convResp = await fetch('{{ url('/user/sms/conversation') }}/' + convId, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin'
                    });
                    const convHtml = await convResp.text();
                    try {
                        const parser = new DOMParser();
                        const parsed = parser.parseFromString(convHtml, 'text/html');
                        const frag = parsed.getElementById('inboxFragment');
                        inboxContent.innerHTML = frag ? frag.innerHTML : parsed.body.innerHTML;
                        hideInboxLoader();
                    } catch (err) {
                        inboxContent.innerHTML = convHtml;
                        hideInboxLoader();
                    }
                    // reattach handlers and update UI
                    ensureDelegation();
                    attachInboxContentHandlers();
                    await updateUnreadBadge();
                    inboxScrollToBottom();

                    // If the server returned an empty conversation (race), retry once after 300ms
                    const mc = document.getElementById('messagesContainer');
                    if (mc && mc.innerText && mc.innerText.trim().toLowerCase().includes('no messages yet')) {
                        // retry once after slight delay
                        await new Promise(r => setTimeout(r, 300));
                        const retryResp = await fetch('{{ url('/user/sms/conversation') }}/' + convId, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            credentials: 'same-origin'
                        });
                        const retryHtml = await retryResp.text();
                        try {
                            const parser2 = new DOMParser();
                            const parsed2 = parser2.parseFromString(retryHtml, 'text/html');
                            const frag2 = parsed2.getElementById('inboxFragment');
                            inboxContent.innerHTML = frag2 ? frag2.innerHTML : parsed2.body.innerHTML;
                        } catch (err) {
                            inboxContent.innerHTML = retryHtml;
                        }
                        ensureDelegation();
                        attachInboxContentHandlers();
                        await updateUnreadBadge();
                        inboxScrollToBottom();
                    }
                } catch (err) {
                    console.error('Failed to load conversation', err);
                    inboxContent.innerHTML = '<p class="text-red-400 text-center py-6">Failed to load conversation.</p>';
                    hideInboxLoader();
                }
            }

            // Append a single message DOM node to messagesContainer (optimistic UI)
            function appendMessageToContainer(msg) {
                try {
                    let mc = document.getElementById('messagesContainer');
                    if (!mc) return;
                    // build images html
                    let imagesHTML = '';
                    try {
                        const images = msg.image ? JSON.parse(msg.image) : [];
                        if (Array.isArray(images) && images.length > 0) {
                            imagesHTML = '<div class="flex flex-wrap gap-2 mt-1">' + images.map(img =>
                                `<img src="/storage/${img}" class="rounded-lg max-w-full">`).join('') + '</div>';
                        }
                    } catch (e) {
                        imagesHTML = '';
                    }

                    const senderClass = (msg.sender_type === 'user') ? 'justify-end' : 'justify-start';
                    const bubbleBg = (msg.sender_type === 'user') ? 'bg-blue-600' : 'bg-gray-700';

                    const html = `
                        <div class="flex ${senderClass} animate-fade-in">
                            <div class="${bubbleBg} px-4 py-2 max-w-[80%] flex flex-col gap-1 rounded-lg relative">
                                ${msg.message ? `<p>${msg.message}</p>` : ''}
                                ${imagesHTML}
                                <span class="text-gray-300 text-xs self-end">Now</span>
                            </div>
                        </div>
                    `;

                    mc.insertAdjacentHTML('beforeend', html);
                    inboxScrollToBottom();
                } catch (err) {
                    console.debug('appendMessageToContainer failed', err);
                }
            }

            // Render image files into a preview container (before upload)
            // Keeps a mutable array of selected files on the file input element as `_selectedFiles`
            function renderImagePreviews(files, previewEl, fileInput = null) {
                try {
                    if (!previewEl) return;
                    previewEl.innerHTML = '';
                    const filesArray = Array.isArray(files) ? files.slice() : (files ? Array.from(files) : []);

                    // If we have a fileInput, persist the selection so we can remove items later
                    if (fileInput) {
                        fileInput._selectedFiles = filesArray.slice();
                    }

                    if (!filesArray || filesArray.length === 0) return;

                    filesArray.forEach((file, idx) => {
                        if (!file.type || !file.type.startsWith('image/')) return;

                        const url = URL.createObjectURL(file);

                        const wrapper = document.createElement('div');
                        wrapper.className = 'relative';
                        wrapper.style.width = '120px';

                        const img = document.createElement('img');
                        img.src = url;
                        img.className = 'rounded-lg w-[120px] h-[120px] object-cover border border-gray-600';
                        img.setAttribute('data-temp-url', url);

                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className =
                            'absolute -top-2 -right-2 bg-red-600 text-white rounded-full w-6 h-6 text-xs flex items-center justify-center remove-preview';
                        btn.setAttribute('data-index', idx);
                        btn.setAttribute('aria-label', 'Remove image');
                        btn.textContent = '×';

                        wrapper.appendChild(img);
                        wrapper.appendChild(btn);
                        previewEl.appendChild(wrapper);
                    });
                } catch (err) {
                    console.debug('renderImagePreviews failed', err);
                }
            }

            // Append a local/temporary message built from the form (uses object URLs for local images)
            function appendLocalMessageFromForm(form) {
                try {
                    const mc = document.getElementById('messagesContainer');
                    if (!mc) return;
                    const textarea = form.querySelector('textarea[name="message"]');
                    const inputText = form.querySelector('input[name="message"]');
                    const fileInput = form.querySelector('input[type="file"][name="image[]"]');
                    const text = textarea ? textarea.value : (inputText ? inputText.value : '');
                    let imagesHTML = '';
                    // Prefer mutable _selectedFiles (reflects removals). Fallback to fileInput.files
                    const sourceFiles = (fileInput && fileInput._selectedFiles) ? fileInput._selectedFiles : (fileInput &&
                        fileInput.files ? Array.from(fileInput.files) : []);
                    if (sourceFiles && sourceFiles.length > 0) {
                        const files = sourceFiles.filter(f => f.type && f.type.startsWith('image/'));
                        if (files.length > 0) {
                            imagesHTML = '<div class="flex flex-wrap gap-2 mt-1">' + files.map(f => {
                                const url = URL.createObjectURL(f);
                                return `<img src="${url}" class="rounded-lg max-w-[120px] max-h-[120px] object-cover border border-gray-600" data-temp-url="${url}">`;
                            }).join('') + '</div>';
                        }
                    }

                    const tempId = 'temp-' + Date.now() + '-' + Math.floor(Math.random() * 10000);
                    const html = `
                        <div class="flex justify-end animate-fade-in temp-message" data-temp-id="${tempId}">
                            <div class="bg-blue-600 px-4 py-2 max-w-[80%] flex flex-col gap-1 rounded-lg relative">
                                ${text ? `<p>${text}</p>` : ''}
                                ${imagesHTML}
                                <span class="text-gray-300 text-xs self-end">Sending…</span>
                            </div>
                        </div>
                    `;

                    mc.insertAdjacentHTML('beforeend', html);
                    inboxScrollToBottom();
                    return tempId;
                } catch (err) {
                    console.debug('appendLocalMessageFromForm failed', err);
                    return null;
                }
            }

            // Robust event delegation on inboxContent so handlers survive innerHTML replacement
            let delegationAttached = false;

            function ensureDelegation() {
                if (delegationAttached || !inboxContent) return;
                delegationAttached = true;

                // Delegate clicks for New and Close buttons
                inboxContent.addEventListener('click', async (e) => {
                    // DEBUG: log delegated clicks inside inboxContent
                    // console will show which element was clicked when diagnosing close/new issues
                    console.debug('inboxContent click:', e.target);
                    const newBtn = e.target.closest('#newMessageBtn');
                    if (newBtn) {
                        e.preventDefault();
                        e.stopPropagation();
                        // reuse existing logic: fetch create fragment into inboxContent
                        inboxContent.innerHTML = '';
                        showInboxLoader();
                        try {
                            const resp = await fetch('{{ route('user.messages.create') }}', {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });
                            const txt = await resp.text();
                            try {
                                const parser = new DOMParser();
                                const parsed = parser.parseFromString(txt, 'text/html');
                                const frag = parsed.getElementById('inboxFragment');
                                inboxContent.innerHTML = frag ? frag.innerHTML : parsed.body.innerHTML;
                                hideInboxLoader();
                            } catch (err) {
                                inboxContent.innerHTML = txt;
                                hideInboxLoader();
                            }
                            attachInboxContentHandlers();
                            await updateUnreadBadge();
                            inboxScrollToBottom();
                        } catch (err) {
                            console.error('Failed to load create fragment', err);
                            inboxContent.innerHTML = '<p class="text-red-400 text-center mt-4">Failed to load.</p>';
                        }
                        return;
                    }

                    const convLink = e.target.closest('.openConversation');
                    if (convLink) {
                        e.preventDefault();
                        e.stopPropagation();
                        const convId = convLink.dataset.conversationId || convLink.getAttribute(
                            'data-conversation-id');
                        if (!convId) return;
                        // load conversation fragment into the dropdown
                        // keep existing content visible and show overlay loader
                        showInboxLoader();
                        try {
                            await loadConversationIntoInbox(convId);
                        } catch (err) {
                            console.error('Failed to load conversation', err);
                            inboxContent.innerHTML =
                                '<p class="text-red-400 text-center py-6">Failed to load conversation.</p>';
                        }
                        return;
                    }

                    const closeBtn = e.target.closest('#closeInboxBtn');
                    if (closeBtn) {
                        console.debug('closeBtn clicked');
                        e.preventDefault();
                        e.stopPropagation();

                        // Add fade-out animation
                        inboxDropdown.classList.add('animate-fadeOut');

                        // Wait for animation to finish
                        setTimeout(async () => {
                            inboxDropdown.classList.add('hidden');
                            inboxDropdown.classList.remove('animate-fadeOut');

                            // Reload inbox content (so when reopened, it’s fresh)
                            try {
                                inboxContent.innerHTML = '';
                                showInboxLoader();
                                const response = await fetch('{{ route('user.messages.index') }}', {
                                    headers: {
                                        'X-Requested-With': 'XMLHttpRequest'
                                    }
                                });
                                const html = await response.text();
                                const parser = new DOMParser();
                                const parsed = parser.parseFromString(html, 'text/html');
                                const frag = parsed.getElementById('inboxFragment');
                                inboxContent.innerHTML = frag ? frag.innerHTML : parsed.body.innerHTML;
                                attachInboxContentHandlers();
                                await updateUnreadBadge();
                            } catch (err) {
                                console.error('Failed to reload inbox', err);
                            }
                        }, 250); // match CSS animation duration
                        return;
                    }

                });

                // Delegate form submit inside inboxContent
                inboxContent.addEventListener('submit', async (e) => {
                    console.debug('inboxContent form submit');
                    const form = e.target.closest('form');
                    if (!form) return;
                    e.preventDefault();
                    e.stopPropagation();

                    // Client-side guard: require at least text or image
                    try {
                        const fileInputForPreview = form.querySelector('input[type="file"][name="image[]"]');
                        const messageNode = form.querySelector('textarea[name="message"]') || form.querySelector(
                            'input[name="message"]');
                        const messageText = messageNode ? (messageNode.value || '').trim() : '';
                        const filesCount = (fileInputForPreview && (fileInputForPreview._selectedFiles ?
                            fileInputForPreview._selectedFiles.length :
                            (fileInputForPreview.files ? fileInputForPreview.files.length : 0))) || 0;

                        // Remove previous error
                        const container = messageNode.closest('div') || messageNode.parentNode;
                        const existingError = container.querySelector('.inline-form-error');
                        if (existingError) existingError.remove();

                        if (!messageText && filesCount === 0) {
                            // Add small inline error under input
                            const errorEl = document.createElement('p');
                            errorEl.className = 'inline-form-error text-red-500 text-xs mt-1';
                            errorEl.textContent = 'Please enter a message';
                            container.appendChild(errorEl);

                            // Highlight the input border temporarily
                            messageNode.classList.add('border-red-500', 'ring-1', 'ring-red-500');

                            // Remove error and border after 2 seconds
                            setTimeout(() => {
                                errorEl.remove();
                                messageNode.classList.remove('border-red-500', 'ring-1', 'ring-red-500');
                            }, 2000);

                            return;
                        } else {
                            // Remove red border if previously added
                            messageNode.classList.remove('border-red-500', 'ring-1', 'ring-red-500');
                        }
                    } catch (err) {
                        console.debug('client-side validation failed', err);
                    }

                    // If there are images or text, optimistically append a local message so user stays in conversation
                    try {
                        const fileInputForPreview = form.querySelector('input[type="file"][name="image[]"]');
                        const messageTextForPreview = form.querySelector('textarea[name="message"]') ? form
                            .querySelector('textarea[name="message"]').value : '';
                        if ((fileInputForPreview && fileInputForPreview.files && fileInputForPreview.files.length >
                                0) || (messageTextForPreview && messageTextForPreview.trim().length > 0)) {
                            const tempId = appendLocalMessageFromForm(form);
                            if (tempId) form.dataset.appendedLocalId = tempId;
                            form.dataset.appendedLocal = '1';
                        }
                    } catch (err) {
                        console.debug('optimistic append check failed', err);
                    }

                    const formData = new FormData(form);
                    try {
                        const resp = await fetch(form.action, {
                            method: form.method || 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            credentials: 'same-origin'
                        });

                        // Expect JSON response for AJAX submissions
                        let data = null;
                        try {
                            data = await resp.json();
                        } catch (jsonErr) {
                            console.debug('Response was not JSON', jsonErr);
                        }

                        if (resp.ok && data && data.success) {
                            // Append server's canonical message so image URLs are correct and message is visible immediately
                            if (data.message) {
                                try {
                                    appendMessageToContainer(data.message);
                                } catch (err) {
                                    console.debug('append server message failed', err);
                                }
                            }

                            // If we appended an optimistic local message, remove it now
                            if (form.dataset.appendedLocal) {
                                try {
                                    const tempId = form.dataset.appendedLocalId;
                                    if (tempId) {
                                        const tempEl = document.querySelector('[data-temp-id="' + tempId + '"]');
                                        if (tempEl && tempEl.parentNode) tempEl.parentNode.removeChild(tempEl);
                                        delete form.dataset.appendedLocalId;
                                    } else {
                                        const temps = document.querySelectorAll('.temp-message');
                                        temps.forEach(t => t.parentNode && t.parentNode.removeChild(t));
                                    }
                                } catch (removeErr) {
                                    console.debug('failed to remove temp message', removeErr);
                                }
                                delete form.dataset.appendedLocal;
                            }

                            // Clear preview thumbnails and revoke object URLs, clear inputs
                            try {
                                const preview = (form && (form.querySelector('#imagePreview') || form.querySelector(
                                        '#previewContainer'))) || inboxContent.querySelector('#imagePreview') ||
                                    inboxContent.querySelector('#previewContainer');
                                if (preview) {
                                    preview.querySelectorAll('img[data-temp-url]').forEach(img => {
                                        const u = img.getAttribute('data-temp-url');
                                        if (u) URL.revokeObjectURL(u);
                                    });
                                    preview.innerHTML = '';
                                }
                                const fileInput = form.querySelector('input[type="file"][name="image[]"]');
                                if (fileInput) {
                                    try {
                                        fileInput.value = '';
                                    } catch (e) {}
                                    if (fileInput._selectedFiles) fileInput._selectedFiles = [];
                                }
                                const ta = form.querySelector('textarea[name="message"]');
                                if (ta) ta.value = '';
                                const inp = form.querySelector('input[name="message"]');
                                if (inp) inp.value = '';
                            } catch (cleanupErr) {
                                console.debug('cleanup previews failed', cleanupErr);
                            }

                            // Refresh conversation in background (shows loader overlay)
                            try {
                                showInboxLoader();
                                await loadConversationIntoInbox(data.conversation_id);
                            } catch (err) {
                                console.error('Failed to load conversation fragment', err);
                                // fallback: close the dropdown
                                inboxDropdown.classList.add('animate-slideUp');
                                setTimeout(() => {
                                    inboxDropdown.classList.add('hidden');
                                    inboxDropdown.classList.remove('animate-slideUp');
                                }, 250);
                            }
                        } else {
                            inboxContent.innerHTML =
                                '<p class="text-red-400 text-center py-4">Failed to send message.</p>';
                        }
                    } catch (err) {
                        console.error('Failed to submit form', err);
                        inboxContent.innerHTML =
                            '<p class="text-red-400 text-center py-4">Failed to send message.</p>';
                    }
                });

                // Delegate change events (file input preview) so handler works for injected fragments
                inboxContent.addEventListener('change', (e) => {
                    try {
                        const target = e.target;
                        if (!target) return;
                        if (target.matches && target.matches('input[type="file"][name="image[]"]')) {
                            // Prefer preview inside the same form, fallback to common preview areas
                            const form = target.closest('form');
                            let previewEl = null;
                            if (form) {
                                previewEl = form.querySelector('#imagePreview') || form.querySelector(
                                    '#previewContainer');
                            }
                            if (!previewEl) {
                                previewEl = inboxContent.querySelector('#imagePreview') || inboxContent.querySelector(
                                    '#previewContainer');
                            }
                            console.debug('file input changed, files:', target.files && target.files.length);
                            renderImagePreviews(target.files, previewEl, target);
                        }
                    } catch (err) {
                        console.debug('delegated file change handler failed', err);
                    }
                });

                // Delegate click on remove-preview buttons to remove an image from selection
                inboxContent.addEventListener('click', (e) => {
                    try {
                        const rem = e.target.closest && e.target.closest('.remove-preview');
                        if (!rem) return;
                        e.preventDefault();
                        // Prevent this click from bubbling to document which may close the dropdown
                        e.stopPropagation();
                        const idx = parseInt(rem.getAttribute('data-index'), 10);
                        // Find file input nearby
                        const wrapper = rem.closest('div');
                        const form = rem.closest('form');
                        const fileInput = form ? form.querySelector('input[type="file"][name="image[]"]') : inboxContent
                            .querySelector('input[type="file"][name="image[]"]');
                        const previewEl = form ? (form.querySelector('#imagePreview') || form.querySelector(
                            '#previewContainer')) : (inboxContent.querySelector('#imagePreview') || inboxContent
                            .querySelector('#previewContainer'));
                        if (!fileInput) {
                            // nothing to update (probably a remote preview) — just remove the preview element
                            if (wrapper && wrapper.parentNode) wrapper.parentNode.removeChild(wrapper);
                            return;
                        }

                        const current = fileInput._selectedFiles ? fileInput._selectedFiles.slice() : (fileInput.files ?
                            Array.from(fileInput.files) : []);
                        if (!current || current.length === 0) return;
                        if (idx < 0 || idx >= current.length) return;
                        current.splice(idx, 1);

                        // Update the file input.files using DataTransfer
                        try {
                            const dt = new DataTransfer();
                            current.forEach(f => dt.items.add(f));
                            fileInput.files = dt.files;
                            fileInput._selectedFiles = current;
                        } catch (err) {
                            // If DataTransfer not available, clear the input and mark removed by rebuild on submit
                            console.debug('DataTransfer update failed', err);
                            fileInput._selectedFiles = current;
                        }

                        // Re-render previews from updated list
                        renderImagePreviews(current, previewEl, fileInput);
                    } catch (err) {
                        console.debug('remove preview handler failed', err);
                    }
                });
            }

            // Ensure delegation is active as soon as possible
            ensureDelegation();

            if (openInboxBtn && inboxDropdown) {
                let isAnimating = false;

                openInboxBtn.addEventListener('click', async (e) => {
                    e.stopPropagation();
                    if (isAnimating) return;

                    const isVisible = !inboxDropdown.classList.contains('hidden');
                    document.querySelectorAll('.dropdown-active').forEach(el => el.classList.add('hidden'));

                    if (!isVisible) {
                        inboxDropdown.classList.remove('hidden');
                        inboxDropdown.classList.add('animate-slideDown');
                        isAnimating = true;
                        setTimeout(() => {
                            inboxDropdown.classList.remove('animate-slideDown');
                            isAnimating = false;
                        }, 250);
                    } else {
                        inboxDropdown.classList.add('animate-slideUp');
                        isAnimating = true;
                        setTimeout(() => {
                            inboxDropdown.classList.add('hidden');
                            inboxDropdown.classList.remove('animate-slideUp');
                            isAnimating = false;
                        }, 250);
                        return;
                    }

                    inboxDropdown.classList.add('dropdown-active');

                    // Load inbox content (parse fragment if the response is a full document)
                    try {
                        inboxContent.innerHTML = '<p class="text-gray-400 text-center py-6">Loading inbox...</p>';
                        const response = await fetch('{{ route('user.messages.index') }}', {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        const html = await response.text();
                        // Try to parse returned HTML and extract an element with id "inboxFragment"
                        let parsed;
                        try {
                            const parser = new DOMParser();
                            parsed = parser.parseFromString(html, 'text/html');
                        } catch (e) {
                            parsed = null;
                        }

                        let fragmentHtml = html;
                        if (parsed) {
                            const frag = parsed.getElementById('inboxFragment');
                            if (frag) {
                                fragmentHtml = frag.innerHTML;
                            } else if (parsed.body) {
                                // If the response is a full document but doesn't include the fragment, use body content
                                fragmentHtml = parsed.body.innerHTML;
                            }
                        }

                        inboxContent.innerHTML = fragmentHtml;
                        hideInboxLoader();
                        // Wire up any buttons inside the loaded fragment (New, Close)
                        ensureDelegation();
                    } catch (err) {
                        inboxContent.innerHTML =
                            '<p class="text-red-400 text-center py-6">Failed to load inbox.</p>';
                        console.error('Failed to load inbox fragment', err);
                    }
                });

                // Hide dropdown when clicking outside
                document.addEventListener('click', (e) => {
                    if (!openInboxBtn.contains(e.target) && !inboxDropdown.contains(e.target)) {
                        if (!inboxDropdown.classList.contains('hidden')) {
                            inboxDropdown.classList.add('animate-slideUp');
                            setTimeout(() => {
                                inboxDropdown.classList.add('hidden');
                                inboxDropdown.classList.remove('animate-slideUp');
                            }, 250);
                        }
                    }
                });
            }

            // Attach handlers for controls inside the inbox content after it's injected
            function attachInboxContentHandlers() {
                // "New" button inside the loaded inbox fragment
                const newBtn = inboxContent.querySelector('#newMessageBtn');
                if (newBtn) {
                    // Load the "create" fragment into the dropdown so it appears top-right like the inbox
                    newBtn.addEventListener('click', async (ev) => {
                        ev.preventDefault();
                        ev.stopPropagation();
                        inboxContent.innerHTML = '<p class="text-gray-400 text-center mt-4">Loading...</p>';
                        try {
                            const resp = await fetch('{{ route('user.messages.create') }}', {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });
                            const txt = await resp.text();
                            try {
                                const parser = new DOMParser();
                                const parsed = parser.parseFromString(txt, 'text/html');
                                const frag = parsed.getElementById('inboxFragment');
                                inboxContent.innerHTML = frag ? frag.innerHTML : parsed.body.innerHTML;
                            } catch (e) {
                                inboxContent.innerHTML = txt;
                            }
                            attachInboxContentHandlers();
                        } catch (err) {
                            console.error('Failed to load create fragment', err);
                            inboxContent.innerHTML = '<p class="text-red-400 text-center mt-4">Failed to load.</p>';
                        }
                    });
                }
                // We use delegated handlers (ensureDelegation) attached earlier so
                // individual re-attaching is not required here.
                // "Close" button inside the loaded inbox fragment
                const closeBtn = inboxContent.querySelector('#closeInboxBtn');
                if (closeBtn) {
                    closeBtn.addEventListener('click', async (ev) => {
                        ev.preventDefault();
                        ev.stopPropagation();

                        // Instead of fully closing, reload the inbox list into the dropdown
                        try {
                            inboxContent.innerHTML = '';
                            showInboxLoader();
                            const response = await fetch('{{ route('user.messages.index') }}', {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });
                            const html = await response.text();
                            try {
                                const parser = new DOMParser();
                                const parsed = parser.parseFromString(html, 'text/html');
                                const frag = parsed.getElementById('inboxFragment');
                                inboxContent.innerHTML = frag ? frag.innerHTML : parsed.body.innerHTML;
                            } catch (e) {
                                inboxContent.innerHTML = html;
                            }
                            // Ensure delegated handlers are active for the newly loaded inbox fragment
                            ensureDelegation();
                        } catch (err) {
                            console.error('Failed to reload inbox', err);
                            // Fallback: simply close the dropdown
                            inboxDropdown.classList.add('animate-slideUp');
                            setTimeout(() => {
                                inboxDropdown.classList.add('hidden');
                                inboxDropdown.classList.remove('animate-slideUp');
                            }, 250);
                        }
                    });
                }

                // Form submissions are handled via delegated listener (ensureDelegation)
                // Image file input preview handling (when create fragment is loaded)
                try {
                    const fileInput = inboxContent.querySelector('input[type="file"][name="image[]"]');
                    const previewContainer = inboxContent.querySelector('#imagePreview');
                    if (fileInput && previewContainer) {
                        fileInput.addEventListener('change', (ev) => {
                            renderImagePreviews(fileInput.files, previewContainer);
                        });
                    }
                } catch (err) {
                    console.debug('attach image preview handler failed', err);
                }
            }
        </script>
    @endif

    <main class="{{ request()->ajax() ? '' : 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8' }}">
        @yield('content')
    </main>

    @yield('modals')
    @yield('scripts')

    <!-- Fade-out Close Button Script -->
    <script>
        document.addEventListener("click", (e) => {
            if (e.target && e.target.id === "closeInboxBtn") {
                // Stop the click from bubbling up (so it won't trigger the open-inbox event)
                e.stopPropagation();

                const inboxDropdown = document.getElementById("inboxDropdown");

                if (inboxDropdown) {
                    // Smooth fade-out animation
                    inboxDropdown.style.transition = "opacity 0.3s ease, transform 0.3s ease";
                    inboxDropdown.style.opacity = "0";
                    inboxDropdown.style.transform = "translateY(-10px)";

                    // Hide fully after animation
                    setTimeout(() => {
                        inboxDropdown.classList.add("hidden");
                        inboxDropdown.style.opacity = "";
                        inboxDropdown.style.transform = "";
                    }, 300);
                }
            }
        });
    </script>
</body>

</html>
