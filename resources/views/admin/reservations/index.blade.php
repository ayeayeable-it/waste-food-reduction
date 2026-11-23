@extends('layouts.app')

@section('title', 'Monitor Reservations — RescueBite Admin')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-brand-dark">Reservation Monitoring</h1>
            <p class="text-sm text-slate-500">Audit all buyer reservations and collection outcomes.</p>
        </div>

        <form method="GET" class="flex flex-wrap gap-3">
            <input type="text" name="reference" value="{{ request('reference') }}" placeholder="Search reference..." class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm bg-white">
            <select name="status" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm bg-white">
                <option value="">All Statuses</option>
                @foreach (['pending', 'collected', 'cancelled', 'expired'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-lg bg-brand-teal px-4 py-1.5 text-sm font-semibold text-white">Filter</button>
        </form>

        <div class="rounded-xl bg-white border border-slate-200 shadow-sm overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Reference</th>
                        <th class="px-4 py-3">Buyer</th>
                        <th class="px-4 py-3">Food &amp; Store</th>
                        <th class="px-4 py-3">Qty / Total</th>
                        <th class="px-4 py-3">Reserved / Expires</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($reservations as $res)
                        <tr>
                            <td class="px-4 py-3 font-mono font-semibold text-brand-dark">{{ $res->reservation_reference }}</td>
                            <td class="px-4 py-3">{{ $res->buyer->name }}</td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-slate-800">{{ $res->listing->food_name }}</p>
                                <p class="text-xs text-slate-500">{{ $res->listing->store->name }}</p>
                            </td>
                            <td class="px-4 py-3">{{ $res->quantity }} · ${{ number_format($res->total_discounted_price, 2) }}</td>
                            <td class="px-4 py-3 text-xs text-slate-600">
                                {{ $res->reserved_at->format('M d, H:i') }} &rarr; {{ $res->expires_at->format('H:i') }}
                            </td>
                            <td class="px-4 py-3 capitalize font-semibold">{{ $res->status }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div>{{ $reservations->links() }}</div>
    </div>
@endsection
