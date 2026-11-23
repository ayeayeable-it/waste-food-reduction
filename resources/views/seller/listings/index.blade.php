@extends('layouts.app')

@section('title', 'My Surplus Listings — RescueBite')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-brand-dark">My Surplus Food Listings</h1>
                <p class="text-sm text-slate-500">Publish, update quantities, or close surplus food items.</p>
            </div>
            <a href="{{ route('seller.listings.create') }}" class="rounded-lg bg-brand-primary px-4 py-2 text-sm font-semibold text-white hover:bg-brand-dark transition">
                + Create Food Listing
            </a>
        </div>

        <form method="GET" action="{{ route('seller.listings.index') }}" class="rounded-xl bg-white p-4 border border-slate-200 flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-slate-600">Store</label>
                <select name="store_id" class="mt-1 rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                    <option value="">All My Stores</option>
                    @foreach ($stores as $store)
                        <option value="{{ $store->id }}" {{ request('store_id') == $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600">Status</label>
                <select name="status" class="mt-1 rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                    <option value="">All Statuses</option>
                    @foreach (['active', 'sold_out', 'closed', 'expired'] as $status)
                        <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="rounded-lg bg-brand-teal px-4 py-1.5 text-sm font-semibold text-white hover:bg-brand-dark">Filter</button>
            <a href="{{ route('seller.listings.index') }}" class="text-sm text-slate-500 hover:underline py-1.5">Reset</a>
        </form>

        @if ($listings->isEmpty())
            <div class="rounded-2xl bg-white p-10 text-center border border-slate-200 shadow-sm">
                <p class="text-slate-600">No surplus food listings found.</p>
            </div>
        @else
            <div class="rounded-xl bg-white border border-slate-200 shadow-sm overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200 text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Item</th>
                            <th class="px-4 py-3">Store &amp; Category</th>
                            <th class="px-4 py-3">Pricing</th>
                            <th class="px-4 py-3">Qty</th>
                            <th class="px-4 py-3">Deadline</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($listings as $listing)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        @if ($listing->image_path)
                                            <img src="{{ asset('storage/' . $listing->image_path) }}" alt="{{ $listing->food_name }}" class="h-12 w-12 rounded-lg object-cover border border-slate-200">
                                        @else
                                            <div class="h-12 w-12 rounded-lg bg-green-50 flex items-center justify-center text-xs font-bold text-brand-dark border border-green-100">
                                                IMG
                                            </div>
                                        @endif
                                        <div>
                                            <p class="font-semibold text-slate-800">{{ $listing->food_name }}</p>
                                            <p class="text-xs text-slate-500">{{ $listing->expiry_label }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="text-slate-800">{{ $listing->store->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $listing->category->name }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="font-bold text-brand-dark">${{ number_format($listing->discounted_price, 2) }}</span>
                                    <span class="block text-xs text-slate-400 line-through">${{ number_format($listing->original_price, 2) }}</span>
                                </td>
                                <td class="px-4 py-3 font-semibold">{{ $listing->available_quantity }}</td>
                                <td class="px-4 py-3 text-xs text-slate-600">{{ $listing->collection_deadline->format('M d, H:i') }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize
                                        {{ $listing->status === 'active' ? 'bg-green-100 text-brand-dark' : 'bg-slate-200 text-slate-600' }}">
                                        {{ str_replace('_', ' ', $listing->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                                    <a href="{{ route('seller.listings.edit', $listing) }}" class="text-brand-teal font-semibold hover:underline">Edit</a>
                                    @if ($listing->status !== 'closed')
                                        <form method="POST" action="{{ route('seller.listings.close', $listing) }}" class="inline" onsubmit="return confirm('Close this listing? Buyers will no longer be able to reserve it.');">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-red-600 font-semibold hover:underline">Close</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div>{{ $listings->links() }}</div>
        @endif
    </div>
@endsection
