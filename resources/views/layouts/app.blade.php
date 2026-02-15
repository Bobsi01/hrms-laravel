<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle ?? 'Dashboard' }} — {{ config('hrms.company.name', 'HRMS') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;450;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="bg-slate-50 min-h-screen font-sans antialiased">
    <div id="layoutRoot" class="flex min-h-screen">
        {{-- Sidebar --}}
        @include('components.sidebar')

        {{-- Content Area --}}
        <div class="flex-1 flex flex-col">
            {{-- Top Bar --}}
            <header class="top-bar">
                <div class="flex items-center gap-3">
                    <button class="md:hidden p-1.5 rounded-lg hover:bg-gray-100 text-gray-500" onclick="document.getElementById('mnav').classList.toggle('hidden')" title="Menu">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <div class="page-title">{{ $pageTitle ?? 'Dashboard' }}</div>
                </div>
                <div class="flex items-center gap-4 relative">
                    <div id="headerClock" class="hidden sm:flex items-center text-sm text-gray-600 select-none" title="Current date & time"></div>
                    @auth
                    {{-- Notifications Bell --}}
                    <div class="relative">
                        <button id="btnNotif" class="relative p-2 rounded hover:bg-gray-100" title="Notifications"
                            data-feed-url="{{ route('notifications.feed') }}"
                            data-mark-all-url="{{ route('notifications.markAllRead') }}"
                            data-mark-url="{{ url('notifications') }}"
                            data-csrf="{{ csrf_token() }}"
                            data-view-all="{{ route('notifications.index') }}">
                            <svg class="w-6 h-6 text-gray-700" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            <span class="absolute -top-0.5 -right-0.5 bg-red-600 text-white text-[10px] leading-4 px-1 rounded-full min-w-[18px] text-center hidden" data-notif-badge></span>
                        </button>
                        <div id="notifDropdown" class="hidden absolute right-0 top-full mt-3 w-[min(360px,calc(100vw-2rem))] sm:w-[420px] z-50">
                            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
                                <div class="flex items-center justify-between px-4 py-3 border-b bg-slate-50">
                                    <h2 class="text-base font-semibold text-slate-900">Notifications</h2>
                                    <button id="notifMarkAll" class="text-xs font-semibold text-indigo-600 hover:text-indigo-500 hover:underline disabled:text-slate-400 disabled:hover:no-underline disabled:cursor-not-allowed" type="button" disabled>Mark all as read</button>
                                </div>
                                <div id="notifList" class="max-h-96 overflow-y-auto bg-white" data-state="idle">
                                    <div id="notifItems" class="divide-y divide-slate-100"></div>
                                    <div id="notifEmpty" class="px-6 py-10 text-center text-sm text-slate-500 hidden">You're all caught up.</div>
                                </div>
                                <div class="px-4 py-3 border-t bg-white text-center">
                                    <a id="notifViewAll" class="text-sm font-medium text-indigo-600 hover:text-indigo-500" href="{{ route('notifications.index') }}">View all</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- User Menu --}}
                    <div class="relative">
                        <button id="btnUser" class="inline-flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-gray-50 transition-colors">
                            <span class="user-avatar">{{ strtoupper(substr(Auth::user()->full_name ?? 'U', 0, 1)) }}</span>
                            <span class="hidden sm:flex flex-col items-start">
                                <span class="text-sm font-medium text-gray-800 leading-4">{{ Auth::user()->full_name }}</span>
                                <span class="text-[10px] text-gray-400 leading-3 capitalize">{{ Auth::user()->role }}</span>
                            </span>
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div id="userMenu" class="absolute right-0 mt-2 w-64 bg-white border border-slate-200 rounded-2xl shadow-xl shadow-slate-200/50 hidden overflow-hidden z-50">
                            <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/60">
                                <div class="text-sm font-semibold text-slate-800">{{ Auth::user()->full_name }}</div>
                                <div class="text-xs text-slate-400 capitalize">{{ Auth::user()->role }}</div>
                            </div>
                            <div class="py-1.5">
                                <div class="px-4 py-1.5 text-[10px] uppercase tracking-widest text-slate-400 font-semibold">Self Service</div>
                                <a class="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors" href="{{ route('dashboard') }}">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7m-9 14V9m0 10h6a2 2 0 002-2v-5m-8 7H7a2 2 0 01-2-2v-5"/></svg>
                                    Home
                                </a>
                                <a class="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors" href="{{ route('attendance.my') }}">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2zm4-7l2 2 4-4"/></svg>
                                    My Attendance
                                </a>
                                <a class="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors" href="{{ route('payroll.my-payslips') }}">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-3 0-5 1.5-5 4s2 4 5 4 5-1.5 5-4-2-4-5-4zm0-5v5m0 8v5"/></svg>
                                    My Payslips
                                </a>
                                <a class="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors" href="{{ route('leave.index') }}">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 7.5L3 10l7.5 2.5L21 12l-10.5 4.5V21l3-3m-3-10.5V3"/></svg>
                                    Leaves
                                </a>
                            </div>
                            <div class="border-t border-slate-100 py-1.5">
                                <div class="px-4 py-1.5 text-[10px] uppercase tracking-widest text-slate-400 font-semibold">Account</div>
                                <a class="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors" href="{{ route('account.profile') }}">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    Account Settings
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="flex items-center gap-2.5 w-full px-4 py-2.5 text-sm font-medium text-red-500 hover:bg-red-50 hover:text-red-600 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                        {{-- Flash Notifications --}}
                        <div id="notifHost" class="absolute right-0 top-full mt-2 z-[70] flex flex-col items-end gap-2 pointer-events-none">
                            @if(session('success'))
                            <div class="notif pointer-events-auto shadow-lg rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-3 py-2 text-sm flex items-center justify-between min-w-[280px] max-w-[520px]" role="alert" data-kind="success" data-autoclose="1" data-timeout="5000">
                                <div class="pr-2">{{ session('success') }}</div>
                                <button class="ml-4 text-emerald-700/70 hover:text-emerald-900" data-close aria-label="Close notification">&times;</button>
                            </div>
                            @endif
                            @if(session('error'))
                            <div class="notif pointer-events-auto shadow-lg rounded-lg border border-red-200 bg-red-50 text-red-800 px-3 py-2 text-sm flex items-center justify-between min-w-[280px] max-w-[520px]" role="alert" data-kind="error" data-autoclose="1">
                                <div class="pr-2">{{ session('error') }}</div>
                                <button class="ml-4 text-red-700/70 hover:text-red-900" data-close aria-label="Close notification">&times;</button>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endauth
                </div>
            </header>

            {{-- Mobile Nav --}}
            @include('components.mobile-nav')

            {{-- Main Content --}}
            <main id="appMain" class="relative p-3 sm:p-5 space-y-4 flex-1">
                <div id="contentLoader" class="hidden absolute inset-0 bg-white/70 backdrop-blur-sm flex items-center justify-center z-10">
                    <div class="loader-spinner"></div>
                </div>
                @yield('content')
            </main>

            <footer class="mt-auto px-5 py-3 text-xs text-gray-400 border-t border-gray-100">&copy; {{ date('Y') }} {{ config('hrms.company.name') }}</footer>
        </div>
    </div>

    {{-- Authorization Modal --}}
    @include('components.modals.authorization')
    {{-- Confirm Modal --}}
    @include('components.modals.confirm')

    @stack('scripts')
</body>
</html>
