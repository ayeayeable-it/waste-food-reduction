@extends('layouts.app')

@section('title', 'Monitor Listings — RescueBite Admin')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-brand-dark">Monitor Food Listings</h1>
            <p class="text-sm text-slate-500">Inspect active surplus listings across all stores and close non-compliant listings.</p>
        </div>

        <form method="GET" class="flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search food name..." class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm bg-white">
            <select name="status" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm bg-white">
                <option value="">All Statuses</option>
                @foreach (['active', 'sold_out', 'closed', 'expired'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-lg bg-brand-teal px-4 py-1.5 text-sm font-semibold text-white">Filter</button>
        </form>

        <div class="rounded-xl bg-white border border-slate-200 shadow-sm overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Food</th>
                        <th class="px-4 py-3">Store / Seller</th>
                        <th class="px-4 py-3">Price</th>
                        <th class="px-4 py-3">Qty</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($listings as $listing)
                        <tr>
                            <td class="px-4 py-3">
                                <p class="font-semibold text-slate-800">{{ $listing->food_name }}</p>
                                <p class="text-xs text-slate-500">{{ $listing->category->name }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-slate-800">{{ $listing->store->name }}</p>
                                <p class="text-xs text-slate-500">{{ $listing->store->seller->name }}</p>
                            </td>
                            <td class="px-4 py-3 font-semibold text-brand-dark">${{ number_format($listing->discounted_price, 2) }}</td>
                            <td class="px-4 py-3">{{ $listing->available_quantity }}</td>
                            <td class="px-4 py-3 capitalize">{{ str_replace('_', ' ', $listing->status) }}</td>
                            <td class="px-4 py-3 text-right">
                                @if ($listing->status !== 'closed')
                                    <form method="POST" action="{{ route('admin.listings.close', $listing) }}" class="inline" onsubmit="return confirm('Force close this listing?');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-xs font-semibold text-red-600 hover:underline">Close</button>
                                    </form>
                                @else
                                    <span class="text-xs text-slate-400">Closed</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div>{{ $listings->links() }}</div>
    </div>
@endsection
