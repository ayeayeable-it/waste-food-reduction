@extends('layouts.app')

@section('title', 'My Reservations — RescueBite')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-brand-dark">My Reservations</h1>
                <p class="text-sm text-slate-500">Track active reservations, collection deadlines, and your collected savings.</p>
            </div>
            <div class="flex gap-3">
                <div class="rounded-xl bg-white px-4 py-2 border border-slate-200 text-right">
                    <p class="text-[11px] uppercase text-slate-500">Collected Units</p>
                    <p class="text-lg font-bold text-brand-dark">{{ $totalCollectedUnits }}</p>
                </div>
                <div class="rounded-xl bg-white px-4 py-2 border border-slate-200 text-right">
                    <p class="text-[11px] uppercase text-slate-500">Total Savings</p>
                    <p class="text-lg font-bold text-brand-primary">${{ number_format($totalSavings, 2) }}</p>
                </div>
            </div>
        </div>

        <form method="GET" class="flex flex-wrap gap-3 items-center">
            <select name="status" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm bg-white">
                <option value="">All Statuses</option>
                @foreach (['pending', 'collected', 'cancelled', 'expired'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-lg bg-brand-teal px-4 py-1.5 text-sm font-semibold text-white hover:bg-brand-dark">Filter</button>
            <a href="{{ route('buyer.reservations.index') }}" class="text-xs text-slate-500 hover:underline">Reset</a>
        </form>

        @if ($reservations->isEmpty())
            <div class="rounded-2xl bg-white p-10 text-center border border-slate-200 shadow-sm">
                <p class="text-slate-600">You have no reservations yet.</p>
                <a href="{{ route('buyer.discover') }}" class="mt-4 inline-block rounded-lg bg-brand-primary px-5 py-2 text-sm font-semibold text-white hover:bg-brand-dark">
                    Discover Surplus Food
                </a>
            </div>
        @else
            <div class="rounded-xl bg-white border border-slate-200 shadow-sm overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200 text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Reference</th>
                            <th class="px-4 py-3">Food &amp; Store</th>
                            <th class="px-4 py-3">Qty</th>
                            <th class="px-4 py-3">Total Price</th>
                            <th class="px-4 py-3">Expires At</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($reservations as $res)
                            <tr>
                                <td class="px-4 py-3 font-mono font-semibold text-brand-dark">{{ $res->reservation_reference }}</td>
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-slate-800">{{ $res->listing->food_name }}</p>
                                    <p class="text-xs text-slate-500">{{ $res->listing->store->name }}</p>
                                </td>
                                <td class="px-4 py-3">{{ $res->quantity }}</td>
                                <td class="px-4 py-3">
                                    <span class="font-bold text-brand-dark">${{ number_format($res->total_discounted_price, 2) }}</span>
                                    <span class="block text-xs text-brand-primary">Save ${{ number_format($res->savings, 2) }}</span>
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-600">{{ $res->expires_at->format('M d, H:i') }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize
                                        {{ $res->status === 'collected' ? 'bg-green-100 text-brand-dark' : ($res->status === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-slate-200 text-slate-600') }}">
                                        {{ $res->status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                                    <a href="{{ route('buyer.reservations.show', $res) }}" class="text-brand-teal font-semibold hover:underline">Details</a>
                                    @if ($res->status === 'pending')
                                        <form method="POST" action="{{ route('buyer.reservations.cancel', $res) }}" class="inline" onsubmit="return confirm('Cancel this reservation?');">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-red-600 font-semibold hover:underline">Cancel</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div>{{ $reservations->links() }}</div>
        @endif
    </div>
@endsection
