@extends('layouts.app')

@section('title', 'Store Reservations & Collection — RescueBite')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-brand-dark">Store Reservations &amp; Collection Confirmation</h1>
            <p class="text-sm text-slate-500">Enter a buyer's reservation reference to confirm in-store food collection.</p>
        </div>

        <!-- Quick Collection Confirmation Box -->
        <div class="rounded-2xl bg-white p-6 border-2 border-brand-primary/30 shadow-sm">
            <h2 class="text-base font-bold text-brand-dark">Confirm In-Store Collection</h2>
            <p class="text-xs text-slate-500 mt-0.5">Partial collection is not supported. Verify the buyer's reference before handing over the order.</p>
            <form method="POST" action="{{ route('seller.reservations.confirm') }}" class="mt-4 flex flex-col sm:flex-row gap-3">
                @csrf
                <input type="text" name="reservation_reference" value="{{ old('reservation_reference') }}" required
                       placeholder="Enter Reference (e.g., RB-20261123-XXXXXX)"
                       class="flex-1 rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-mono uppercase focus:border-brand-primary focus:outline-none">
                <button type="submit" class="rounded-lg bg-brand-primary px-6 py-2.5 text-sm font-semibold text-white hover:bg-brand-dark transition">
                    Confirm Collection
                </button>
            </form>
        </div>

        <!-- Filter Reservations -->
        <form method="GET" action="{{ route('seller.reservations.index') }}" class="flex flex-wrap gap-3 items-center">
            <input type="text" name="reference" value="{{ request('reference') }}" placeholder="Search reference..."
                   class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm bg-white">
            <select name="status" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm bg-white">
                <option value="">All Statuses</option>
                @foreach (['pending', 'collected', 'cancelled', 'expired'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-lg bg-brand-teal px-4 py-1.5 text-sm font-semibold text-white hover:bg-brand-dark">Filter</button>
            <a href="{{ route('seller.reservations.index') }}" class="text-xs text-slate-500 hover:underline">Reset</a>
        </form>

        @if ($reservations->isEmpty())
            <div class="rounded-2xl bg-white p-10 text-center border border-slate-200 shadow-sm">
                <p class="text-slate-600">No reservations found for your stores.</p>
            </div>
        @else
            <div class="rounded-xl bg-white border border-slate-200 shadow-sm overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200 text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Reference</th>
                            <th class="px-4 py-3">Buyer</th>
                            <th class="px-4 py-3">Food &amp; Store</th>
                            <th class="px-4 py-3">Qty / Total</th>
                            <th class="px-4 py-3">Expires</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($reservations as $res)
                            <tr>
                                <td class="px-4 py-3 font-mono font-semibold text-brand-dark">{{ $res->reservation_reference }}</td>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-slate-800">{{ $res->buyer->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $res->buyer->email }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-slate-800">{{ $res->listing->food_name }}</p>
                                    <p class="text-xs text-slate-500">{{ $res->listing->store->name }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    {{ $res->quantity }} &times; ${{ number_format($res->unit_price, 2) }}
                                    <span class="block font-bold text-brand-dark">${{ number_format($res->total_discounted_price, 2) }}</span>
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-600">{{ $res->expires_at->format('M d, H:i') }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize
                                        {{ $res->status === 'collected' ? 'bg-green-100 text-brand-dark' : ($res->status === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-slate-200 text-slate-600') }}">
                                        {{ $res->status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if ($res->status === 'pending' && $res->expires_at->isFuture())
                                        <form method="POST" action="{{ route('seller.reservations.confirm') }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="reservation_reference" value="{{ $res->reservation_reference }}">
                                            <button type="submit" class="rounded-lg bg-brand-primary px-3 py-1.5 text-xs font-semibold text-white hover:bg-brand-dark">
                                                Mark Collected
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-slate-400">—</span>
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
