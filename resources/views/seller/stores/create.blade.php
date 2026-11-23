@extends('layouts.app')

@section('title', 'Register Store — RescueBite')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('seller.stores.index') }}" class="text-sm text-brand-teal hover:underline">&larr; Back to My Stores</a>
            <h1 class="mt-2 text-2xl font-bold text-brand-dark">Register a New Store</h1>
            <p class="text-sm text-slate-500">Click on the map to pin your store's exact geographical coordinates.</p>
        </div>

        <form method="POST" action="{{ route('seller.stores.store') }}" class="rounded-2xl bg-white p-6 sm:p-8 border border-slate-200 shadow-sm space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700">Store Name</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary/20">
                </div>
                <div>
                    <label for="phone" class="block text-sm font-medium text-slate-700">Contact Phone</label>
                    <input id="phone" name="phone" type="text" value="{{ old('phone') }}"
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary/20">
                </div>
            </div>

            <div>
                <label for="address" class="block text-sm font-medium text-slate-700">Full Store Address</label>
                <input id="address" name="address" type="text" value="{{ old('address') }}" required
                       class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary/20">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Pin Store Location on Map</label>
                <div id="store-map" class="h-64 w-full rounded-xl border border-slate-300 z-10"></div>
                <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="latitude" class="block text-xs font-medium text-slate-600">Latitude</label>
                        <input id="latitude" name="latitude" type="number" step="0.0000001" value="{{ old('latitude', '51.5073509') }}" required
                               class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label for="longitude" class="block text-xs font-medium text-slate-600">Longitude</label>
                        <input id="longitude" name="longitude" type="number" step="0.0000001" value="{{ old('longitude', '-0.1277583') }}" required
                               class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                </div>
            </div>

            <div>
                <label for="collection_instructions" class="block text-sm font-medium text-slate-700">In-Store Collection Instructions</label>
                <textarea id="collection_instructions" name="collection_instructions" rows="3"
                          placeholder="e.g., Show your reservation reference at checkout counter 1."
                          class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary/20">{{ old('collection_instructions') }}</textarea>
            </div>

            <div class="flex items-center gap-2">
                <input id="is_active" name="is_active" type="checkbox" value="1" checked class="h-4 w-4 rounded border-slate-300 text-brand-primary">
                <label for="is_active" class="text-sm text-slate-700">Store is active and open for reservations</label>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('seller.stores.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancel</a>
                <button type="submit" class="rounded-lg bg-brand-primary px-6 py-2 text-sm font-semibold text-white hover:bg-brand-dark transition">
                    Save Store
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const latInput = document.getElementById('latitude');
            const lngInput = document.getElementById('longitude');
            const initialLat = parseFloat(latInput.value) || 51.5073509;
            const initialLng = parseFloat(lngInput.value) || -0.1277583;

            const map = L.map('store-map').setView([initialLat, initialLng], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            const marker = L.marker([initialLat, initialLng], { draggable: true }).addTo(map);

            const updateInputs = (lat, lng) => {
                latInput.value = lat.toFixed(7);
                lngInput.value = lng.toFixed(7);
            };

            marker.on('dragend', () => {
                const pos = marker.getLatLng();
                updateInputs(pos.lat, pos.lng);
            });

            map.on('click', (e) => {
                marker.setLatLng(e.latlng);
                updateInputs(e.latlng.lat, e.latlng.lng);
            });
        });
    </script>
@endpush
