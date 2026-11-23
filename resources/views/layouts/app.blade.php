<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'RescueBite — Surplus Food Reservation')</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="min-h-screen flex flex-col bg-brand-bg text-brand-text">
    @auth
        @php
            $unreadNotificationsCount = auth()->user()->appNotifications()->whereNull('read_at')->count();
            $headerNotifications = auth()->user()->appNotifications()->latest()->take(5)->get();
        @endphp
    @endauth

    <header class="bg-brand-surface border-b border-slate-200 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-brand-primary text-white font-bold text-lg">
                        RB
                    </span>
                    <span class="text-xl font-bold tracking-tight text-brand-dark">RescueBite</span>
                </a>

                <nav class="hidden md:flex items-center gap-4 text-sm font-medium text-slate-600">
                    @auth
                        @if (auth()->user()->isBuyer())
                            <a href="{{ route('buyer.dashboard') }}" class="hover:text-brand-primary transition">Dashboard</a>
                            <a href="{{ route('buyer.discover') }}" class="hover:text-brand-primary transition">Discover</a>
                            <a href="{{ route('buyer.map') }}" class="hover:text-brand-primary transition">Map</a>
                            <a href="{{ route('buyer.reservations.index') }}" class="hover:text-brand-primary transition">Reservations</a>
                        @elseif (auth()->user()->isSeller())
                            <a href="{{ route('seller.dashboard') }}" class="hover:text-brand-primary transition">Dashboard</a>
                            <a href="{{ route('seller.stores.index') }}" class="hover:text-brand-primary transition">Stores</a>
                            <a href="{{ route('seller.listings.index') }}" class="hover:text-brand-primary transition">Listings</a>
                            <a href="{{ route('seller.reservations.index') }}" class="hover:text-brand-primary transition">Reservations</a>
                            <a href="{{ route('seller.reports.index') }}" class="hover:text-brand-primary transition">Reports</a>
                        @elseif (auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="hover:text-brand-primary transition">Dashboard</a>
                            <a href="{{ route('admin.users.index') }}" class="hover:text-brand-primary transition">Users</a>
                            <a href="{{ route('admin.stores.index') }}" class="hover:text-brand-primary transition">Stores</a>
                            <a href="{{ route('admin.categories.index') }}" class="hover:text-brand-primary transition">Categories</a>
                            <a href="{{ route('admin.listings.index') }}" class="hover:text-brand-primary transition">Listings</a>
                            <a href="{{ route('admin.reservations.index') }}" class="hover:text-brand-primary transition">Reservations</a>
                            <a href="{{ route('admin.reports.index') }}" class="hover:text-brand-primary transition">Reports</a>
                            <a href="{{ route('admin.audit-logs.index') }}" class="hover:text-brand-primary transition">Audit</a>
                        @endif

                        <!-- Notification Dropdown -->
                        <div class="relative">
                            <button id="notif-dropdown-btn" type="button" class="relative inline-flex items-center p-1.5 rounded-lg text-slate-600 hover:bg-slate-100" aria-label="Notifications">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                @if ($unreadNotificationsCount > 0)
                                    <span class="absolute -top-0.5 -right-0.5 inline-flex h-4 min-w-[16px] items-center justify-center rounded-full bg-red-600 px-1 text-[10px] font-bold text-white">
                                        {{ $unreadNotificationsCount > 9 ? '9+' : $unreadNotificationsCount }}
                                    </span>
                                @endif
                            </button>

                            <div id="notif-dropdown-menu" class="hidden absolute right-0 mt-2 w-80 rounded-xl bg-white border border-slate-200 shadow-lg z-50 overflow-hidden">
                                <div class="px-4 py-2.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                                    <span class="text-xs font-bold text-slate-700 uppercase">Notifications</span>
                                    <a href="{{ route('notifications.index') }}" class="text-xs font-semibold text-brand-primary hover:underline">View All</a>
                                </div>
                                <div class="max-h-72 overflow-y-auto divide-y divide-slate-100">
                                    @forelse ($headerNotifications as $notif)
                                        <div class="px-4 py-3 text-xs {{ $notif->isRead() ? 'bg-white' : 'bg-green-50/60' }}">
                                            <p class="font-semibold text-slate-800">{{ $notif->title }}</p>
                                            <p class="text-slate-600 mt-0.5 line-clamp-2">{{ $notif->message }}</p>
                                            <p class="text-[10px] text-slate-400 mt-1">{{ $notif->created_at->diffForHumans() }}</p>
                                        </div>
                                    @empty
                                        <p class="px-4 py-6 text-center text-xs text-slate-500">No notifications yet.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('profile.edit') }}" class="hover:text-brand-primary transition">Profile</a>

                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition">
                                Logout
                            </button>
                        </form>
                    @else
                        <a href="{{ route('home') }}" class="hover:text-brand-primary transition">Home</a>
                        <a href="{{ route('login') }}" class="hover:text-brand-primary transition">Login</a>
                        <a href="{{ route('register') }}" class="rounded-lg bg-brand-primary px-4 py-2 text-white font-semibold hover:bg-brand-dark transition">
                            Register
                        </a>
                    @endauth
                </nav>

                <button id="mobile-menu-button" type="button" class="md:hidden inline-flex items-center justify-center p-2 rounded-md text-slate-600 hover:bg-slate-100" aria-label="Toggle Menu">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>

        <div id="mobile-menu" class="hidden md:hidden border-t border-slate-200 bg-white px-4 py-3 space-y-2 text-sm">
            <a href="{{ route('home') }}" class="block py-1 text-slate-700">Home</a>
            @auth
                @if (auth()->user()->isBuyer())
                    <a href="{{ route('buyer.dashboard') }}" class="block py-1 text-slate-700">Dashboard</a>
                    <a href="{{ route('buyer.discover') }}" class="block py-1 text-slate-700">Discover</a>
                    <a href="{{ route('buyer.map') }}" class="block py-1 text-slate-700">Map</a>
                    <a href="{{ route('buyer.reservations.index') }}" class="block py-1 text-slate-700">My Reservations</a>
                @elseif (auth()->user()->isSeller())
                    <a href="{{ route('seller.dashboard') }}" class="block py-1 text-slate-700">Dashboard</a>
                    <a href="{{ route('seller.stores.index') }}" class="block py-1 text-slate-700">My Stores</a>
                    <a href="{{ route('seller.listings.index') }}" class="block py-1 text-slate-700">My Listings</a>
                    <a href="{{ route('seller.reservations.index') }}" class="block py-1 text-slate-700">Reservations</a>
                    <a href="{{ route('seller.reports.index') }}" class="block py-1 text-slate-700">Reports</a>
                @elseif (auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="block py-1 text-slate-700">Admin Dashboard</a>
                    <a href="{{ route('admin.users.index') }}" class="block py-1 text-slate-700">Users</a>
                    <a href="{{ route('admin.stores.index') }}" class="block py-1 text-slate-700">Stores</a>
                    <a href="{{ route('admin.categories.index') }}" class="block py-1 text-slate-700">Categories</a>
                    <a href="{{ route('admin.listings.index') }}" class="block py-1 text-slate-700">Listings</a>
                    <a href="{{ route('admin.reservations.index') }}" class="block py-1 text-slate-700">Reservations</a>
                    <a href="{{ route('admin.notifications.index') }}" class="block py-1 text-slate-700">Broadcasts</a>
                    <a href="{{ route('admin.reports.index') }}" class="block py-1 text-slate-700">Reports</a>
                    <a href="{{ route('admin.audit-logs.index') }}" class="block py-1 text-slate-700">Audit Logs</a>
                @endif
                <a href="{{ route('notifications.index') }}" class="block py-1 text-slate-700">
                    Notifications ({{ $unreadNotificationsCount }} unread)
                </a>
                <a href="{{ route('profile.edit') }}" class="block py-1 text-slate-700">Profile</a>
                <form method="POST" action="{{ route('logout') }}" class="pt-2 border-t border-slate-100">
                    @csrf
                    <button type="submit" class="w-full text-left py-1 font-semibold text-red-600">Logout</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="block py-1 text-slate-700">Login</a>
                <a href="{{ route('register') }}" class="block py-1 font-semibold text-brand-primary">Register</a>
            @endauth
        </div>
    </header>

    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <x-flash-messages />
        @yield('content')
    </main>

    <footer class="bg-white border-t border-slate-200 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col sm:flex-row justify-between items-center gap-2 text-xs text-slate-500">
            <p>&copy; {{ date('Y') }} RescueBite. Location-Aware Surplus Food Reservation System.</p>
            <p>Disclaimer: Sellers are solely responsible for food accuracy, allergens, and availability.</p>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const notifBtn = document.getElementById('notif-dropdown-btn');
            const notifMenu = document.getElementById('notif-dropdown-menu');
            if (notifBtn && notifMenu) {
                notifBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    notifMenu.classList.toggle('hidden');
                });
                document.addEventListener('click', () => notifMenu.classList.add('hidden'));
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
