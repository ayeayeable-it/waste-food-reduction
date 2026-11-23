@extends('layouts.app')

@section('title', $listing->food_name . ' — RescueBite')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@section('content')
    <div class="max-w-5xl mx-auto space-y-6">
        <a href="{{ route('buyer.discover') }}" class="text-sm font-medium text-brand-teal hover:underline">
            &larr; Back to Food Discovery
        </a>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left: Food Information -->
            <div class="lg:col-span-2 space-y-6">
                <div class="rounded-2xl bg-white border border-slate-200 shadow-sm overflow-hidden">
                    <div class="h-64 bg-slate-100 relative">
                        @if ($listing->image_path)
                            <img src="{{ asset('storage/' . $listing->image_path) }}" alt="{{ $listing->food_name }}" class="h-full w-full object-cover">
                        @else
                            <div class="h-full w-full flex items-center justify-center bg-green-50 text-brand-dark font-semibold">
                                {{ $listing->category->name }}
                            </div>
                        @endif
                        <span class="absolute top-4 left-4 rounded-full bg-brand-primary px-3 py-1 text-xs font-bold text-white shadow">
                            Save {{ $listing->discount_percentage }}%
                        </span>
                    </div>

                    <div class="p-6 space-y-4">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-semibold uppercase text-brand-teal">{{ $listing->category->name }}</p>
                                <h1 class="mt-1 text-2xl font-bold text-brand-dark">{{ $listing->food_name }}</h1>
                            </div>
                            <div class="text-right">
                                <p class="text-2xl font-bold text-brand-primary">${{ number_format($listing->discounted_price, 2) }}</p>
                                <p class="text-xs text-slate-400 line-through">${{ number_format($listing->original_price, 2) }}</p>
                            </div>
                        </div>

                        <p class="text-sm text-slate-700 leading-relaxed">{{ $listing->description }}</p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t border-slate-100 text-sm">
                            <div class="rounded-xl bg-slate-50 p-4 border border-slate-100">
                                <p class="text-xs font-semibold uppercase text-slate-500">Expiry / Date Label</p>
                                <p class="mt-1 font-medium text-slate-800">{{ $listing->expiry_label }}</p>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-4 border border-slate-100">
                                <p class="text-xs font-semibold uppercase text-slate-500">Collection Deadline</p>
                                <p class="mt-1 font-medium text-amber-700">{{ $listing->collection_deadline->format('M d, Y \a\t H:i') }}</p>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-4 border border-slate-100">
                                <p class="text-xs font-semibold uppercase text-slate-500">Storage Instructions</p>
                                <p class="mt-1 text-slate-700">{{ $listing->storage_instructions }}</p>
                            </div>
                            <div class="rounded-xl bg-amber-50 p-4 border border-amber-200">
                                <p class="text-xs font-semibold uppercase text-amber-800">Allergen Information</p>
                                <p class="mt-1 text-amber-900">{{ $listing->allergen_info }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Reservation Form & Store Details -->
            <div class="space-y-6">
                <div class="rounded-2xl bg-white p-6 border border-slate-200 shadow-sm space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-semibold text-slate-700">Availability</span>
                        <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $listing->isEligibleForReservation() ? 'bg-green-100 text-brand-dark' : 'bg-red-100 text-red-700' }}">
                            {{ $listing->isEligibleForReservation() ? $listing->available_quantity . ' units left' : 'Unavailable' }}
                        </span>
                    </div>

                    @if ($listing->isEligibleForReservation())
                        <form method="POST" action="{{ route('buyer.reservations.store', $listing) }}" class="space-y-4">
                            @csrf

                            <div>
                                <label for="quantity" class="block text-sm font-medium text-slate-700">Quantity to Reserve</label>
                                <input id="quantity" name="quantity" type="number" min="1" max="{{ $listing->available_quantity }}" value="{{ old('quantity', 1) }}" required
                                       class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-primary focus:outline-none">
                                <p class="mt-1 text-xs text-slate-500">Pay in-store upon collection. Hold lasts up to 3 hours or until collection deadline.</p>
                            </div>

                            <div class="rounded-xl bg-slate-50 p-4 border border-slate-200 space-y-2.5 text-xs text-slate-700">
                                <p class="font-bold text-slate-800">Required Food Acknowledgement</p>

                                <label class="flex items-start gap-2 cursor-pointer">
                                    <input type="checkbox" name="acknowledged_product_info" value="1" required class="mt-0.5 rounded border-slate-300 text-brand-primary">
                                    <span>I have read the product description.</span>
                                </label>
                                <label class="flex items-start gap-2 cursor-pointer">
                                    <input type="checkbox" name="acknowledged_expiry" value="1" required class="mt-0.5 rounded border-slate-300 text-brand-primary">
                                    <span>I have checked the expiry/date label ({{ $listing->expiry_label }}).</span>
                                </label>
                                <label class="flex items-start gap-2 cursor-pointer">
                                    <input type="checkbox" name="acknowledged_allergens" value="1" required class="mt-0.5 rounded border-slate-300 text-brand-primary">
                                    <span>I have reviewed the allergen information.</span>
                                </label>
                                <label class="flex items-start gap-2 cursor-pointer">
                                    <input type="checkbox" name="acknowledged_storage" value="1" required class="mt-0.5 rounded border-slate-300 text-brand-primary">
                                    <span>I have read the storage instructions.</span>
                                </label>
                                <label class="flex items-start gap-2 cursor-pointer">
                                    <input type="checkbox" name="acknowledged_collection" value="1" required class="mt-0.5 rounded border-slate-300 text-brand-primary">
                                    <span>I understand the in-store collection instructions and deadline.</span>
                                </label>
                                <label class="flex items-start gap-2 cursor-pointer pt-1 border-t border-slate-200">
                                    <input type="checkbox" name="disclaimer_accepted" value="1" required class="mt-0.5 rounded border-slate-300 text-brand-primary">
                                    <span class="text-slate-600">I understand that RescueBite does not independently verify food safety or physical stock availability; the seller is solely responsible.</span>
                                </label>
                            </div>

                            <button type="submit" class="w-full rounded-lg bg-brand-primary py-2.5 text-sm font-semibold text-white hover:bg-brand-dark transition">
                                Confirm Reservation
                            </button>
                        </form>
                    @else
                        <div class="rounded-xl bg-red-50 p-4 border border-red-200 text-xs text-red-800">
                            This listing is currently unavailable for new reservations (status: <strong>{{ $listing->status }}</strong>).
                        </div>
                    @endif
                </div>

                <div class="rounded-2xl bg-white p-6 border border-slate-200 shadow-sm space-y-3">
                    <h2 class="text-base font-bold text-brand-dark">{{ $listing->store->name }}</h2>
                    <p class="text-xs text-slate-600">{{ $listing->store->address }}</p>
                    @if ($distanceKm !== null)
                        <p class="text-xs font-semibold text-brand-teal">Approx. {{ number_format($distanceKm, 2) }} km from your selected location</p>
                    @endif
                    @if ($listing->store->collection_instructions)
                        <div class="rounded-lg bg-green-50 p-3 text-xs text-brand-dark border border-green-100">
                            <strong>Collection Instructions:</strong> {{ $listing->store->collection_instructions }}
                        </div>
                    @endif
                    <div id="store-mini-map" class="h-44 w-full rounded-xl border border-slate-200 z-10"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const lat = {{ (float) $listing->store->latitude }};
            const lng = {{ (float) $listing->store->longitude }};
            const map = L.map('store-mini-map').setView([lat, lng], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);
            L.marker([lat, lng]).addTo(map).bindPopup(@json($listing->store->name)).openPopup();
        });
    </script>
@endpush
