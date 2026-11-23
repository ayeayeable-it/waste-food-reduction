<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Listing;
use App\Services\LocationService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DiscoveryController extends Controller
{
    public function __construct(
        private readonly LocationService $locationService
    ) {}

    /**
     * Browse, search, filter, and sort eligible surplus food listings.
     */
    public function index(Request $request): View
    {
        $hasLocation = $request->filled('lat') && $request->filled('lng')
            && is_numeric($request->input('lat'))
            && is_numeric($request->input('lng'));

        $buyerLat = $hasLocation ? (float) $request->input('lat') : null;
        $buyerLng = $hasLocation ? (float) $request->input('lng') : null;

        $query = Listing::query()
            ->join('stores', 'listings.store_id', '=', 'stores.id')
            ->where('listings.status', 'active')
            ->where('listings.available_quantity', '>', 0)
            ->where('listings.collection_deadline', '>', now())
            ->where('stores.is_active', true)
            ->with(['store', 'category'])
            ->select('listings.*');

        if ($hasLocation) {
            $haversine = $this->locationService->haversineSql('stores.latitude', 'stores.longitude');
            $query->selectRaw("{$haversine} AS distance_km", [$buyerLat, $buyerLng, $buyerLat]);

            if ($request->filled('max_distance') && is_numeric($request->input('max_distance'))) {
                $maxDistance = (float) $request->input('max_distance');
                if ($maxDistance > 0) {
                    $query->having('distance_km', '<=', $maxDistance);
                }
            }
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('listings.food_name', 'like', "%{$search}%")
                    ->orWhere('listings.description', 'like', "%{$search}%")
                    ->orWhere('stores.name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('listings.category_id', $request->integer('category_id'));
        }

        if ($request->filled('min_price') && is_numeric($request->input('min_price'))) {
            $query->where('listings.discounted_price', '>=', (float) $request->input('min_price'));
        }

        if ($request->filled('max_price') && is_numeric($request->input('max_price'))) {
            $query->where('listings.discounted_price', '<=', (float) $request->input('max_price'));
        }

        $sort = (string) $request->input('sort', $hasLocation ? 'nearest' : 'newest');

        match ($sort) {
            'nearest' => $hasLocation
                ? $query->orderBy('distance_km', 'asc')
                : $query->orderByDesc('listings.created_at'),
            'price_asc' => $query->orderBy('listings.discounted_price', 'asc'),
            'price_desc' => $query->orderBy('listings.discounted_price', 'desc'),
            'deadline' => $query->orderBy('listings.collection_deadline', 'asc'),
            default => $query->orderByDesc('listings.created_at'),
        };

        $listings = $query->paginate(9)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('buyer.discover', [
            'listings' => $listings,
            'categories' => $categories,
            'hasLocation' => $hasLocation,
            'buyerLat' => $buyerLat,
            'buyerLng' => $buyerLng,
            'currentSort' => $sort,
        ]);
    }

    /**
     * Interactive Leaflet.js + OpenStreetMap discovery view.
     */
    public function map(Request $request): View
    {
        $hasLocation = $request->filled('lat') && $request->filled('lng')
            && is_numeric($request->input('lat'))
            && is_numeric($request->input('lng'));

        // Default center aligns with seeded stores if buyer has not selected a location yet
        $buyerLat = $hasLocation ? (float) $request->input('lat') : 51.5073509;
        $buyerLng = $hasLocation ? (float) $request->input('lng') : -0.1277583;
        $maxDistance = ($request->filled('max_distance') && is_numeric($request->input('max_distance')))
            ? (float) $request->input('max_distance')
            : null;

        $eligibleListings = Listing::eligible()
            ->with(['store', 'category'])
            ->get();

        $storesMapData = $eligibleListings
            ->groupBy('store_id')
            ->map(function ($storeListings) use ($buyerLat, $buyerLng) {
                $store = $storeListings->first()->store;
                $distanceKm = $this->locationService->calculateDistanceKm(
                    $buyerLat,
                    $buyerLng,
                    (float) $store->latitude,
                    (float) $store->longitude
                );

                return [
                    'id' => $store->id,
                    'name' => $store->name,
                    'address' => $store->address,
                    'latitude' => (float) $store->latitude,
                    'longitude' => (float) $store->longitude,
                    'distance_km' => $distanceKm,
                    'listings' => $storeListings->map(fn (Listing $item) => [
                        'id' => $item->id,
                        'food_name' => $item->food_name,
                        'discounted_price' => number_format((float) $item->discounted_price, 2),
                        'original_price' => number_format((float) $item->original_price, 2),
                        'available_quantity' => $item->available_quantity,
                        'url' => route('buyer.listings.show', [
                            'listing' => $item->id,
                            'lat' => $buyerLat,
                            'lng' => $buyerLng,
                        ]),
                    ])->values()->all(),
                ];
            })
            ->when($maxDistance && $maxDistance > 0, fn ($col) => $col->filter(fn ($s) => $s['distance_km'] <= $maxDistance))
            ->sortBy('distance_km')
            ->values();

        return view('buyer.map', [
            'storesData' => $storesMapData,
            'hasLocation' => $hasLocation,
            'buyerLat' => $buyerLat,
            'buyerLng' => $buyerLng,
            'maxDistance' => $maxDistance,
        ]);
    }

    /**
     * Display full details for a surplus food listing and its store.
     */
    public function show(Request $request, Listing $listing): View
    {
        $listing->load(['store', 'category']);

        $distanceKm = null;
        if ($request->filled('lat') && $request->filled('lng')
            && is_numeric($request->input('lat'))
            && is_numeric($request->input('lng'))) {
            $distanceKm = $this->locationService->calculateDistanceKm(
                (float) $request->input('lat'),
                (float) $request->input('lng'),
                (float) $listing->store->latitude,
                (float) $listing->store->longitude
            );
        }

        return view('buyer.listings.show', [
            'listing' => $listing,
            'distanceKm' => $distanceKm,
        ]);
    }
}
