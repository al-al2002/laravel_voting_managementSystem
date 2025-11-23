<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'VoteMaster')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/votemaster.png') }}">


    {{-- Tailwind + Chart.js --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
        }

        .sidebar-gradient {
            background: linear-gradient(180deg, #09182D 0%, #0F223A 50%, #1A3554 100%);
        }

        .menu-item {
            transition: all 0.3s;
            position: relative;
        }

        .menu-item:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(8px);
        }

        .menu-item.active {
            background: rgba(255, 255, 255, 0.15);
        }

        .menu-item.active::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 0;
            height: 0;
            border-top: 40px solid #f59e0b;
            border-left: 40px solid transparent;
            border-radius: 0 8px 0 0;
            z-index: 1;
        }

        .sidebar-scroll {
            overflow-y: auto;
            flex-grow: 1;
        }

        .content-area {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        }
    </style>
</head>

<body class="bg-gray-100 flex h-screen overflow-hidden">

    {{-- Sidebar --}}
    <div class="sidebar-gradient w-64 shadow-2xl flex flex-col">
        {{-- Logo --}}
        <div class="p-6 border-b border-white border-opacity-20">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 rounded-lg overflow-hidden flex items-center justify-center">
                    <img src="{{ asset('images/votemaster.png') }}" alt="VoteMaster Logo"
                        class="w-full h-full object-contain">
                </div>
                <div>
                    <h1 class="text-yellow-400 font-bold text-lg">VoteMaster</h1>
                    <p class="text-gray-300 text-xs">Admin Panel</p>
                </div>
            </div>
        </div>


        {{-- Admin Info --}}
        <div class="p-6 border-b border-white border-opacity-20">
            <div class="flex items-center space-x-3">
                @php
                    $admin = App\Models\Admin::find(session('auth_id'));
                @endphp
                <div
                    class="w-12 h-12 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-full flex items-center justify-center">
                    <span class="text-white font-semibold text-lg">{{ strtoupper(substr($admin->name, 0, 2)) }}</span>
                </div>
                <div>
                    <h3 class="text-white font-semibold">{{ $admin->name }}</h3>
                    <p class="text-gray-300 text-sm">System Administrator</p>
                    <div class="flex items-center mt-1">
                        <div class="w-2 h-2 bg-green-400 rounded-full mr-2"></div>
                        <span class="text-green-300 text-xs">Online</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Menu --}}
        <nav class="flex-1 py-6 sidebar-scroll">
            <div class="px-4 space-y-2">
                <a href="{{ route('admin.dashboard') }}"
                    class="menu-item flex items-center px-4 py-3 text-white rounded-lg @if (request()->routeIs('admin.dashboard')) active @endif">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                    </svg>
                    Dashboard
                </a>

                <a href="{{ route('admin.elections.index') }}"
                    class="menu-item flex items-center px-4 py-3 text-white rounded-lg @if (request()->routeIs('admin.elections.*')) active @endif">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2z"></path>
                    </svg>
                    Elections
                </a>

                <a href="{{ route('admin.candidates.index') }}"
                    class="menu-item flex items-center px-4 py-3 text-white rounded-lg @if (request()->routeIs('admin.candidates.*')) active @endif">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z">
                        </path>
                    </svg>
                    Candidates
                </a>

                <a href="{{ route('admin.voters.index') }}"
                    class="menu-item flex items-center px-4 py-3 text-white rounded-lg @if (request()->routeIs('admin.voters.*')) active @endif">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-5 h-5 mr-3">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15 19.128a9.38 9.38 0 01-6 0M12 12a4.5 4.5 0 100-9 4.5 4.5 0 000 9zm6.75 4.5a6.75 6.75 0 10-13.5 0h13.5z" />
                    </svg>
                    Voters
                </a>

                <a href="{{ route('admin.results') }}"
                    class="menu-item flex items-center px-4 py-3 text-white rounded-lg @if (request()->routeIs('admin.results')) active @endif">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 17v-6h6v6m-3-9V5m0 0L9 9m3-4l3 4"></path>
                    </svg>
                    Results
                </a>

                <a href="{{ route('admin.live-monitor') }}"
                    class="menu-item flex items-center px-4 py-3 text-white rounded-lg @if (request()->routeIs('admin.live-monitor')) active @endif">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2 7h20v13H2zM8 7l4-4 4 4" />
                    </svg>
                    Live Monitor
                </a>
            </div>
        </nav>

        {{-- Logout --}}
        <div class="p-4 border-t border-white border-opacity-20">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="menu-item flex items-center px-4 py-3 text-white rounded-lg w-full">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                        </path>
                    </svg>
                    Logout
                </button>
            </form>
        </div>
    </div>

    {{-- Main --}}
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white shadow-sm border-b border-gray-200 px-6 py-4 flex justify-between items-center">
            <div>
                <h2 id="pageTitle" class="text-2xl font-bold text-gray-800">@yield('title', 'Dashboard')</h2>
                <p class="text-gray-600 text-sm">Welcome back, manage your voting system</p>
            </div>

            {{-- 💬 SMS Inbox --}}
            <div class="flex items-center gap-6">
                <a href="{{ route('admin.sms.index') }}" class="relative flex items-center">
                    {{-- SMS icon --}}
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                        stroke="currentColor" class="w-6 h-6 text-gray-600 hover:text-[#09182D] transition">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.25 8.25c0-1.243 1.007-2.25 2.25-2.25h15a2.25 2.25 0 012.25 2.25v7.5a2.25 2.25 0 01-2.25 2.25H6.75L2.25 21V8.25z" />
                    </svg>

                    {{-- Unread badge --}}
                    <span id="unreadBadge"
                        class="hidden absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold px-1.5 py-0.5 rounded-full">
                        0
                    </span>
                </a>
            </div>

        </header>

        <main class="flex-1 overflow-y-auto content-area p-6">
            @yield('content')
        </main>
    </div>

    {{-- Auto-refresh unread badge --}}
    <script>
        async function refreshUnreadCount() {
            try {
                const response = await fetch("{{ route('admin.sms.unread-count') }}");
                const data = await response.json();
                const badge = document.getElementById("unreadBadge");

                if (data.count > 0) {
                    badge.textContent = data.count;
                    badge.classList.remove("hidden");
                } else {
                    badge.classList.add("hidden");
                }
            } catch (err) {
                console.error("Failed to refresh unread count:", err);
            }
        }

        // Run immediately and every 5 seconds
        refreshUnreadCount();
        setInterval(refreshUnreadCount, 5000);
    </script>

</body>

</html>
