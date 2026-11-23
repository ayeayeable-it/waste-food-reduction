@extends('layouts.app')

@section('title', 'RescueBite — Save Surplus Food Nearby')

@section('content')
    <section class="rounded-2xl bg-gradient-to-br from-brand-dark via-brand-teal to-brand-primary p-8 sm:p-12 text-white shadow-lg">
        <div class="max-w-2xl">
            <span class="inline-block rounded-full bg-white/15 px-3 py-1 text-xs font-semibold uppercase tracking-wider">
                Location-Aware Food Rescue
            </span>
            <h1 class="mt-4 text-3xl sm:text-5xl font-bold tracking-tight">
                Rescue surplus food from nearby convenience stores.
            </h1>
            <p class="mt-4 text-base sm:text-lg text-green-50">
                Discover discounted surplus items before they go to waste, reserve them online for up to 3 hours, and collect directly in-store.
            </p>
            <div class="mt-6 flex flex-wrap gap-3">
                @auth
                    @if (auth()->user()->isBuyer())
                        <a href="{{ route('buyer.dashboard') }}" class="rounded-lg bg-white px-5 py-2.5 text-sm font-semibold text-brand-dark shadow hover:bg-green-50 transition">
                            Open Buyer Dashboard
                        </a>
                    @elseif (auth()->user()->isSeller())
                        <a href="{{ route('seller.dashboard') }}" class="rounded-lg bg-white px-5 py-2.5 text-sm font-semibold text-brand-dark shadow hover:bg-green-50 transition">
                            Open Seller Dashboard
                        </a>
                    @elseif (auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="rounded-lg bg-white px-5 py-2.5 text-sm font-semibold text-brand-dark shadow hover:bg-green-50 transition">
                            Open Admin Dashboard
                        </a>
                    @endif
                @else
                    <a href="{{ route('register') }}" class="rounded-lg bg-white px-5 py-2.5 text-sm font-semibold text-brand-dark shadow hover:bg-green-50 transition">
                        Get Started Free
                    </a>
                    <a href="{{ route('login') }}" class="rounded-lg border border-white/60 px-5 py-2.5 text-sm font-semibold text-white hover:bg-white/10 transition">
                        Sign In
                    </a>
                @endauth
            </div>
        </div>
    </section>

    <section class="mt-10 grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="rounded-xl bg-white p-6 border border-slate-200 shadow-sm">
            <h2 class="text-lg font-semibold text-brand-dark">1. Locate Nearby Stores</h2>
            <p class="mt-2 text-sm text-slate-600">
                Uses HTML5 Geolocation, OpenStreetMap, and Haversine distance calculations to find surplus food closest to you.
            </p>
        </div>
        <div class="rounded-xl bg-white p-6 border border-slate-200 shadow-sm">
            <h2 class="text-lg font-semibold text-brand-dark">2. Reserve Safely</h2>
            <p class="mt-2 text-sm text-slate-600">
                Transactional stock locking prevents overselling and guarantees your reserved quantity until your collection window expires.
            </p>
        </div>
        <div class="rounded-xl bg-white p-6 border border-slate-200 shadow-sm">
            <h2 class="text-lg font-semibold text-brand-dark">3. Collect In-Store</h2>
            <p class="mt-2 text-sm text-slate-600">
                Present your unique reservation reference at the counter to confirm collection and track your consumer savings.
            </p>
        </div>
    </section>
@endsection
