@extends('layouts.app')

@section('title', 'Buyer Dashboard — RescueBite')

@section('content')
    <div class="space-y-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-brand-dark">Welcome back, {{ $buyer->name }}</h1>
                <p class="text-sm text-slate-500">Discover surplus food near you, track reservations, and view your savings.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('buyer.map') }}" class="rounded-lg border border-brand-teal px-4 py-2 text-sm font-semibold text-brand-teal hover:bg-teal-50 transition">
                    Open Map View
                </a>
                <a href="{{ route('buyer.discover') }}" class="rounded-lg bg-brand-primary px-4 py-2 text-sm font-semibold text-white hover:bg-brand-dark transition">
                    Discover Nearby Food
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="rounded-xl bg-white p-5 border border-slate-200 shadow-sm">
                <p class="text-xs font-medium text-slate-500 uppercase">Pending Reservations</p>
                <p class="mt-2 text-3xl font-bold text-brand-dark">{{ $pendingCount }}</p>
            </div>
            <div class="rounded-xl bg-white p-5 border border-slate-200 shadow-sm">
                <p class="text-xs font-medium text-slate-500 uppercase">Collected Orders</p>
                <p class="mt-2 text-3xl font-bold text-brand-primary">{{ $collectedCount }}</p>
            </div>
            <div class="rounded-xl bg-white p-5 border border-slate-200 shadow-sm">
                <p class="text-xs font-medium text-slate-500 uppercase">Total Savings</p>
                <p class="mt-2 text-3xl font-bold text-brand-teal">${{ number_format($totalSavings, 2) }}</p>
            </div>
            <div class="rounded-xl bg-white p-5 border border-slate-200 shadow-sm">
                <p class="text-xs font-medium text-slate-500 uppercase">Unread Notifications</p>
                <p class="mt-2 text-3xl font-bold text-slate-800">{{ $unreadNotifications }}</p>
            </div>
        </div>

        <div>
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-lg font-semibold text-slate-800">Available Surplus Food</h2>
                <a href="{{ route('buyer.discover') }}" class="text-sm font-semibold text-brand-primary hover:underline">
                    Browse all &rarr;
                </a>
            </div>
            @if ($featuredListings->isEmpty())
                <p class="text-sm text-slate-500">No active surplus food listings are available right now.</p>
            @else
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach ($featuredListings as $listing)
                        <a href="{{ route('buyer.listings.show', $listing) }}" class="group rounded-xl bg-white p-5 border border-slate-200 shadow-sm hover:border-brand-primary transition flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between text-xs">
                                    <span class="rounded-full bg-green-100 px-2.5 py-0.5 font-semibold text-brand-dark">
                                        {{ $listing->category->name }}
                                    </span>
                                    <span class="font-bold text-brand-primary">-{{ $listing->discount_percentage }}%</span>
                                </div>
                                <h3 class="mt-3 text-base font-bold text-slate-800 group-hover:text-brand-primary transition">{{ $listing->food_name }}</h3>
                                <p class="text-xs text-slate-500">{{ $listing->store->name }}</p>
                                <p class="mt-2 text-xs text-slate-600 line-clamp-2">{{ $listing->description }}</p>
                            </div>
                            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                                <div>
                                    <span class="text-lg font-bold text-brand-dark">${{ number_format($listing->discounted_price, 2) }}</span>
                                    <span class="ml-1 text-xs text-slate-400 line-through">${{ number_format($listing->original_price, 2) }}</span>
                                </div>
                                <span class="text-xs font-medium text-slate-600">{{ $listing->available_quantity }} left</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
