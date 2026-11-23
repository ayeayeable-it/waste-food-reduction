<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $buyer = $request->user();

        $recentReservations = $buyer->reservations()
            ->with(['listing.store'])
            ->latest('reserved_at')
            ->take(5)
            ->get();

        $collectedReservations = $buyer->reservations()
            ->where('status', 'collected')
            ->get();

        $totalSavings = $collectedReservations->sum(
            fn ($res) => (float) $res->original_total_price - (float) $res->total_discounted_price
        );

        $featuredListings = Listing::eligible()
            ->with(['store', 'category'])
            ->latest()
            ->take(6)
            ->get();

        return view('buyer.dashboard', [
            'buyer' => $buyer,
            'pendingCount' => $buyer->reservations()->where('status', 'pending')->count(),
            'collectedCount' => $collectedReservations->count(),
            'totalSavings' => round($totalSavings, 2),
            'unreadNotifications' => $buyer->appNotifications()->whereNull('read_at')->count(),
            'categories' => Category::orderBy('name')->get(),
            'featuredListings' => $featuredListings,
            'recentReservations' => $recentReservations,
        ]);
    }
}
