@extends('layouts.app')

@section('title', 'Reservation ' . $reservation->reservation_reference . ' — RescueBite')

@section('content')
    <div class="max-w-3xl mx-auto space-y-6">
        <a href="{{ route('buyer.reservations.index') }}" class="text-sm font-medium text-brand-teal hover:underline">
            &larr; Back to My Reservations
        </a>

        <div class="rounded-2xl bg-white p-6 sm:p-8 border border-slate-200 shadow-sm space-y-6">
            <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-100 pb-5">
                <div>
                    <p class="text-xs font-semibold uppercase text-slate-500">Reservation Reference</p>
                    <h1 class="mt-1 text-2xl sm:text-3xl font-mono font-bold text-brand-dark">{{ $reservation->reservation_reference }}</h1>
                    <p class="mt-1 text-xs text-slate-500">Show this reference to the store staff when collecting your food.</p>
                </div>
                <span class="rounded-full px-3 py-1 text-xs font-bold uppercase
                    {{ $reservation->status === 'collected' ? 'bg-green-100 text-brand-dark' : ($reservation->status === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-slate-200 text-slate-600') }}">
                    {{ $reservation->status }}
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-xs uppercase text-slate-500">Food Item</p>
                    <p class="font-bold text-slate-800">{{ $reservation->listing->food_name }}</p>
                    <p class="text-xs text-slate-500">{{ $reservation->listing->category->name }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase text-slate-500">Store</p>
                    <p class="font-bold text-slate-800">{{ $reservation->listing->store->name }}</p>
                    <p class="text-xs text-slate-500">{{ $reservation->listing->store->address }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase text-slate-500">Quantity &amp; Price</p>
                    <p class="font-bold text-brand-dark">
                        {{ $reservation->quantity }} &times; ${{ number_format($reservation->unit_price, 2) }} = ${{ number_format($reservation->total_discounted_price, 2) }}
                    </p>
                    <p class="text-xs text-brand-primary">Savings: ${{ number_format($reservation->savings, 2) }} (Original: ${{ number_format($reservation->original_total_price, 2) }})</p>
                </div>
                <div>
                    <p class="text-xs uppercase text-slate-500">Collection Window</p>
                    <p class="font-semibold text-amber-700">Expires: {{ $reservation->expires_at->format('M d, Y \a\t H:i') }}</p>
                    <p class="text-xs text-slate-500">Reserved: {{ $reservation->reserved_at->format('M d, H:i') }}</p>
                    @if ($reservation->collected_at)
                        <p class="text-xs text-brand-dark font-medium">Collected: {{ $reservation->collected_at->format('M d, Y H:i') }}</p>
                    @endif
                </div>
            </div>

            @if ($reservation->listing->store->collection_instructions)
                <div class="rounded-xl bg-green-50 p-4 border border-green-100 text-xs text-brand-dark">
                    <strong>Store Collection Instructions:</strong> {{ $reservation->listing->store->collection_instructions }}
                </div>
            @endif

            @if ($reservation->acknowledgement)
                <div class="rounded-xl bg-slate-50 p-4 border border-slate-200 text-xs text-slate-600">
                    Food safety disclaimer &amp; product details acknowledged on <strong>{{ $reservation->acknowledgement->acknowledged_at->format('M d, Y H:i') }}</strong>.
                </div>
            @endif

            @if ($reservation->status === 'pending')
                <div class="pt-2 flex justify-end">
                    <form method="POST" action="{{ route('buyer.reservations.cancel', $reservation) }}" onsubmit="return confirm('Are you sure you want to cancel this reservation?');">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="rounded-lg border border-red-300 bg-red-50 px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-100 transition">
                            Cancel Reservation
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
@endsection
