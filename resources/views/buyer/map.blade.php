@extends('layouts.app')

@section('title', 'Nearby Stores Map — RescueBite')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-brand-dark">Interactive Surplus Food Map</h1>
                <p class="text-sm text-slate-500">Click anywhere on the map to set your location manually, or use GPS to find the nearest stores.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" id="map-gps-btn" class="rounded-lg border border-brand-primary bg-green-50 px-3 py-2 text-xs font-semibold text-brand-dark hover:bg-green-100 transition">
                    Use My GPS Location
                </button>
                <a href="{{ route('buyer.discover', ['lat' => $buyerLat, 'lng' => $buyerLng, 'max_distance' => $maxDistance]) }}"
                   class="rounded-lg bg-brand-primary px-4 py-2 text-xs font-semibold text-white hover:bg-brand-dark transition">
                    List View
                </a>
            </div>
        </div>

        <form id="map-filter-form" method="GET" action="{{ route('buyer.map') }}" class="rounded-xl bg-white p-4 border border-slate-200 flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-medium text-slate-600">Selected Latitude</label>
                <input id="map-lat" type="number" step="0.0000001" name="lat" value="{{ $buyerLat }}" class="mt-1 rounded-lg border border-slate-300 px-3 py-1.5 text-sm w-40">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600">Selected Longitude</label>
                <input id="map-lng" type="number" step="0.0000001" name="lng" value="{{ $buyerLng }}" class="mt-1 rounded-lg border border-slate-300 px-3 py-1.5 text-sm w-40">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600">Max Distance</label>
                <select name="max_distance" class="mt-1 rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                    <option value="">Any Distance</option>
                    @foreach ([1, 2, 5, 10, 25] as $km)
                        <option value="{{ $km }}" @selected($maxDistance == $km)>Within {{ $km }} km</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="rounded-lg bg-brand-teal px-4 py-2 text-xs font-semibold text-white hover:bg-brand-dark">
                Update Distances
            </button>
        </form>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
                <div id="discovery-map" class="h-[480px] w-full rounded-2xl border border-slate-200 shadow-sm z-10"></div>
            </div>

            <div class="rounded-2xl bg-white p-5 border border-slate-200 shadow-sm max-h-[480px] overflow-y-auto space-y-4">
                <h2 class="text-base font-bold text-brand-dark">Nearby Stores ({{ $storesData->count() }})</h2>
                @if ($storesData->isEmpty())
                    <p class="text-sm text-slate-500">No stores with active surplus food within this distance.</p>
                @else
                    @foreach ($storesData as $store)
                        <div class="rounded-xl border border-slate-200 p-4 hover:border-brand-primary transition">
                            <div class="flex items-start justify-between gap-2">
                                <h3 class="text-sm font-bold text-slate-800">{{ $store['name'] }}</h3>
                                <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-brand-dark whitespace-nowrap">
                                    {{ number_format($store['distance_km'], 2) }} km
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $store['address'] }}</p>
                            <ul class="mt-3 space-y-1.5 border-t border-slate-100 pt-2">
                                @foreach ($store['listings'] as $item)
                                    <li class="flex items-center justify-between text-xs">
                                        <a href="{{ $item['url'] }}" class="font-medium text-brand-teal hover:underline">
                                            {{ $item['food_name'] }}
                                        </a>
                                        <span class="font-bold text-slate-800">${{ $item['discounted_price'] }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const buyerLat = {{ $buyerLat }};
            const buyerLng = {{ $buyerLng }};
            const stores = @json($storesData);

            const map = L.map('discovery-map').setView([buyerLat, buyerLng], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            // Buyer location marker
            const buyerMarker = L.circleMarker([buyerLat, buyerLng], {
                radius: 9,
                fillColor: '#0F766E',
                color: '#FFFFFF',
                weight: 3,
                opacity: 1,
                fillOpacity: 0.9
            }).addTo(map).bindPopup('<strong>Your Selected Location</strong>');

            // Store markers
            stores.forEach((store) => {
                const itemsHtml = store.listings.map((item) =>
                    `<div style="margin-top:4px;"><a href="${item.url}" style="color:#16A34A;font-weight:600;text-decoration:underline;">${item.food_name}</a> — $${item.discounted_price} (${item.available_quantity} left)</div>`
                ).join('');

                L.marker([store.latitude, store.longitude])
                    .addTo(map)
                    .bindPopup(`
                        <div style="min-width:180px;">
                            <div style="font-weight:700;color:#166534;">${store.name}</div>
                            <div style="font-size:11px;color:#64748B;">${store.distance_km.toFixed(2)} km away</div>
                            ${itemsHtml}
                        </div>
                    `);
            });

            // Click on map to manually set buyer location
            const latInput = document.getElementById('map-lat');
            const lngInput = document.getElementById('map-lng');
            const form = document.getElementById('map-filter-form');

            map.on('click', (e) => {
                latInput.value = e.latlng.lat.toFixed(7);
                lngInput.value = e.latlng.lng.toFixed(7);
                buyerMarker.setLatLng(e.latlng);
                form.submit();
            });

            document.getElementById('map-gps-btn').addEventListener('click', () => {
                if (!navigator.geolocation) {
                    alert('Geolocation is not supported by your browser. Click directly on the map instead.');
                    return;
                }
                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        latInput.value = pos.coords.latitude.toFixed(7);
                        lngInput.value = pos.coords.longitude.toFixed(7);
                        form.submit();
                    },
                    () => alert('Location permission denied. Click anywhere on the map to set your location manually.')
                );
            });
        });
    </script>
@endpush
