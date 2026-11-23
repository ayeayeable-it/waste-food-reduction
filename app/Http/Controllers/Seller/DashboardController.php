<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $seller = $request->user();
        $storeIds = $seller->stores()->pluck('id');

        $listingsQuery = Listing::whereIn('store_id', $storeIds);
        $reservationsQuery = Reservation::whereHas('listing', fn ($q) => $q->whereIn('store_id', $storeIds));

        return view('seller.dashboard', [
            'seller' => $seller,
            'totalStores' => $storeIds->count(),
            'activeListings' => (clone $listingsQuery)->where('status', 'active')->count(),
            'availableQuantity' => (int) (clone $listingsQuery)->where('status', 'active')->sum('available_quantity'),
            'pendingReservations' => (clone $reservationsQuery)->where('status', 'pending')->count(),
            'collectedReservations' => (clone $reservationsQuery)->where('status', 'collected')->count(),
            'expiredReservations' => (clone $reservationsQuery)->where('status', 'expired')->count(),
            'recentListings' => (clone $listingsQuery)->with(['store', 'category'])->latest()->take(5)->get(),
        ]);
    }
}
