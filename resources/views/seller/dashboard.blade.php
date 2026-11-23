@extends('layouts.app')

@section('title', 'Seller Dashboard — RescueBite')

@section('content')
    <div class="space-y-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-brand-dark">Seller Dashboard — {{ $seller->name }}</h1>
                <p class="text-sm text-slate-500">Overview of your stores, surplus listings, and reservation activity.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('seller.stores.create') }}" class="rounded-lg border border-brand-primary px-4 py-2 text-sm font-semibold text-brand-dark hover:bg-green-50 transition">
                    + Add Store
                </a>
                <a href="{{ route('seller.listings.create') }}" class="rounded-lg bg-brand-primary px-4 py-2 text-sm font-semibold text-white hover:bg-brand-dark transition">
                    + Publish Surplus Food
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="rounded-xl bg-white p-5 border border-slate-200 shadow-sm">
                <p class="text-xs font-medium text-slate-500 uppercase">Total Stores</p>
                <p class="mt-2 text-3xl font-bold text-brand-dark">{{ $totalStores }}</p>
            </div>
            <div class="rounded-xl bg-white p-5 border border-slate-200 shadow-sm">
                <p class="text-xs font-medium text-slate-500 uppercase">Active Listings</p>
                <p class="mt-2 text-3xl font-bold text-brand-primary">{{ $activeListings }}</p>
            </div>
            <div class="rounded-xl bg-white p-5 border border-slate-200 shadow-sm">
                <p class="text-xs font-medium text-slate-500 uppercase">Available Food Units</p>
                <p class="mt-2 text-3xl font-bold text-brand-teal">{{ $availableQuantity }}</p>
            </div>
            <div class="rounded-xl bg-white p-5 border border-slate-200 shadow-sm">
                <p class="text-xs font-medium text-slate-500 uppercase">Pending Reservations</p>
                <p class="mt-2 text-3xl font-bold text-amber-600">{{ $pendingReservations }}</p>
            </div>
            <div class="rounded-xl bg-white p-5 border border-slate-200 shadow-sm">
                <p class="text-xs font-medium text-slate-500 uppercase">Collected Reservations</p>
                <p class="mt-2 text-3xl font-bold text-brand-primary">{{ $collectedReservations }}</p>
            </div>
            <div class="rounded-xl bg-white p-5 border border-slate-200 shadow-sm">
                <p class="text-xs font-medium text-slate-500 uppercase">Expired Reservations</p>
                <p class="mt-2 text-3xl font-bold text-slate-600">{{ $expiredReservations }}</p>
            </div>
        </div>

        <div class="rounded-xl bg-white p-6 border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-800">Your Recent Listings</h2>
                <a href="{{ route('seller.listings.index') }}" class="text-sm font-semibold text-brand-primary hover:underline">
                    View all listings &rarr;
                </a>
            </div>
            @if ($recentListings->isEmpty())
                <p class="mt-3 text-sm text-slate-500">You have not published any surplus food listings yet.</p>
            @else
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-slate-200 text-xs uppercase text-slate-500">
                            <tr>
                                <th class="py-2 pr-4">Food Item</th>
                                <th class="py-2 pr-4">Store</th>
                                <th class="py-2 pr-4">Discounted Price</th>
                                <th class="py-2 pr-4">Qty</th>
                                <th class="py-2 pr-4">Status</th>
                                <th class="py-2 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($recentListings as $listing)
                                <tr>
                                    <td class="py-2.5 pr-4 font-medium text-slate-800">{{ $listing->food_name }}</td>
                                    <td class="py-2.5 pr-4 text-slate-600">{{ $listing->store->name }}</td>
                                    <td class="py-2.5 pr-4 font-semibold text-brand-dark">${{ number_format($listing->discounted_price, 2) }}</td>
                                    <td class="py-2.5 pr-4">{{ $listing->available_quantity }}</td>
                                    <td class="py-2.5 pr-4 capitalize">{{ str_replace('_', ' ', $listing->status) }}</td>
                                    <td class="py-2.5 text-right">
                                        <a href="{{ route('seller.listings.edit', $listing) }}" class="text-brand-teal font-medium hover:underline">Edit</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
