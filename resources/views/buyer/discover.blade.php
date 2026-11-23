@extends('layouts.app')

@section('title', 'Discover Surplus Food — RescueBite')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-brand-dark">Discover Nearby Surplus Food</h1>
                <p class="text-sm text-slate-500">Search discounted items, filter by category or price, and sort by nearest store.</p>
            </div>
            <a href="{{ route('buyer.map', request()->only(['lat', 'lng', 'max_distance'])) }}" class="rounded-lg bg-brand-teal px-4 py-2 text-sm font-semibold text-white hover:bg-brand-dark transition">
                Switch to Map View
            </a>
        </div>

        <!-- Search, Filter & Location Controls -->
        <form id="discover-form" method="GET" action="{{ route('buyer.discover') }}" class="rounded-2xl bg-white p-5 border border-slate-200 shadow-sm space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-slate-600">Search Food or Store</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="e.g., Sourdough, Wrap, GreenMart..."
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-primary focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-600">Category</label>
                    <select name="category_id" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">All Categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-600">Sort By</label>
                    <select name="sort" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="nearest" @selected($currentSort === 'nearest')>Nearest Store (requires location)</option>
                        <option value="newest" @selected($currentSort === 'newest')>Newest Listings</option>
                        <option value="price_asc" @selected($currentSort === 'price_asc')>Price: Low to High</option>
                        <option value="price_desc" @selected($currentSort === 'price_desc')>Price: High to Low</option>
                        <option value="deadline" @selected($currentSort === 'deadline')>Ending Soonest</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end pt-2 border-t border-slate-100">
                <div>
                    <label class="block text-xs font-medium text-slate-600">Min Price ($)</label>
                    <input type="number" step="0.01" min="0" name="min_price" value="{{ request('min_price') }}" placeholder="0.00"
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600">Max Price ($)</label>
                    <input type="number" step="0.01" min="0" name="max_price" value="{{ request('max_price') }}" placeholder="20.00"
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600">Max Distance (km)</label>
                    <select name="max_distance" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Any Distance</option>
                        @foreach ([1, 2, 5, 10, 25] as $km)
                            <option value="{{ $km }}" @selected(request('max_distance') == $km)>Within {{ $km }} km</option>
                        @endforeach
                    </select>
                </div>

                <input type="hidden" id="lat-input" name="lat" value="{{ $buyerLat }}">
                <input type="hidden" id="lng-input" name="lng" value="{{ $buyerLng }}">

                <div class="lg:col-span-2 flex flex-wrap gap-2">
                    <button type="button" id="use-location-btn" class="rounded-lg border border-brand-primary bg-green-50 px-3 py-2 text-xs font-semibold text-brand-dark hover:bg-green-100 transition">
                        Use My Location
                    </button>
                    <button type="button" id="demo-location-btn" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-100 transition">
                        Demo Central Area
                    </button>
                    <button type="submit" class="rounded-lg bg-brand-primary px-5 py-2 text-sm font-semibold text-white hover:bg-brand-dark transition">
                        Apply Filters
                    </button>
                    <a href="{{ route('buyer.discover') }}" class="py-2 px-2 text-xs text-slate-500 hover:underline">Reset</a>
                </div>
            </div>

            <div id="location-status" class="text-xs text-slate-500">
                @if ($hasLocation)
                    <span class="text-brand-dark font-medium">Location active:</span>
                    {{ number_format($buyerLat, 5) }}, {{ number_format($buyerLng, 5) }} — distances calculated via Haversine formula.
                @else
                    Click <strong>Use My Location</strong> (or choose a manual point on <a href="{{ route('buyer.map') }}" class="text-brand-teal underline">Map View</a>) to calculate distances. We do not continuously track your location.
                @endif
            </div>
        </form>

        <!-- Results Grid -->
        @if ($listings->isEmpty())
            <div class="rounded-2xl bg-white p-10 text-center border border-slate-200 shadow-sm">
                <p class="text-slate-600 font-medium">No eligible surplus food listings match your filters.</p>
                <p class="mt-1 text-xs text-slate-500">Try widening your distance radius or clearing your search keywords.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($listings as $listing)
                    <div class="rounded-2xl bg-white border border-slate-200 shadow-sm overflow-hidden flex flex-col justify-between hover:border-brand-primary transition">
                        <div>
                            <div class="relative h-44 bg-slate-100">
                                @if ($listing->image_path)
                                    <img src="{{ asset('storage/' . $listing->image_path) }}" alt="{{ $listing->food_name }}" class="h-full w-full object-cover">
                                @else
                                    <div class="h-full w-full flex items-center justify-center bg-green-50 text-brand-dark font-semibold text-sm">
                                        {{ $listing->category->name }}
                                    </div>
                                @endif
                                <span class="absolute top-3 left-3 rounded-full bg-brand-primary px-2.5 py-1 text-xs font-bold text-white shadow">
                                    -{{ $listing->discount_percentage }}%
                                </span>
                                @if ($hasLocation && isset($listing->distance_km))
                                    <span class="absolute top-3 right-3 rounded-full bg-white/95 px-2.5 py-1 text-xs font-semibold text-brand-dark shadow">
                                        {{ number_format((float) $listing->distance_km, 2) }} km
                                    </span>
                                @endif
                            </div>

                            <div class="p-5">
                                <p class="text-xs font-medium text-brand-teal">{{ $listing->category->name }}</p>
                                <h2 class="mt-1 text-lg font-bold text-slate-800">{{ $listing->food_name }}</h2>
                                <p class="text-xs text-slate-500">{{ $listing->store->name }} · {{ $listing->store->address }}</p>
                                <p class="mt-2 text-xs text-slate-600 line-clamp-2">{{ $listing->description }}</p>
                                <p class="mt-2 text-xs text-amber-700 font-medium">
                                    Collect before: {{ $listing->collection_deadline->format('M d, H:i') }}
                                </p>
                            </div>
                        </div>

                        <div class="px-5 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                            <div>
                                <span class="text-xl font-bold text-brand-dark">${{ number_format($listing->discounted_price, 2) }}</span>
                                <span class="ml-1 text-xs text-slate-400 line-through">${{ number_format($listing->original_price, 2) }}</span>
                                <p class="text-xs text-slate-500">{{ $listing->available_quantity }} available</p>
                            </div>
                            <a href="{{ route('buyer.listings.show', array_filter(['listing' => $listing->id, 'lat' => $buyerLat, 'lng' => $buyerLng])) }}"
                               class="rounded-lg bg-brand-primary px-4 py-2 text-xs font-semibold text-white hover:bg-brand-dark transition">
                                View Details
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <div>{{ $listings->links() }}</div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const useLocBtn = document.getElementById('use-location-btn');
            const demoLocBtn = document.getElementById('demo-location-btn');
            const latInput = document.getElementById('lat-input');
            const lngInput = document.getElementById('lng-input');
            const statusEl = document.getElementById('location-status');
            const form = document.getElementById('discover-form');

            if (useLocBtn) {
                useLocBtn.addEventListener('click', () => {
                    if (!navigator.geolocation) {
                        statusEl.textContent = 'Geolocation is not supported by your browser. Please use Map View to select a location manually.';
                        return;
                    }
                    statusEl.textContent = 'Requesting your location...';
                    navigator.geolocation.getCurrentPosition(
                        (pos) => {
                            latInput.value = pos.coords.latitude.toFixed(7);
                            lngInput.value = pos.coords.longitude.toFixed(7);
                            form.submit();
                        },
                        () => {
                            statusEl.textContent = 'Location permission denied. You can click "Demo Central Area" or pick a point manually on Map View.';
                        },
                        { enableHighAccuracy: false, timeout: 8000 }
                    );
                });
            }

            if (demoLocBtn) {
                demoLocBtn.addEventListener('click', () => {
                    latInput.value = '51.5073509';
                    lngInput.value = '-0.1277583';
                    form.submit();
                });
            }
        });
    </script>
@endpush
