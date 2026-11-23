<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Listing;
use App\Models\Reservation;
use App\Models\Store;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $collected = Reservation::where('status', 'collected')->get();
        $totalConsumerSavings = $collected->sum(
            fn (Reservation $res) => (float) $res->original_total_price - (float) $res->total_discounted_price
        );

        $statusCounts = [
            'pending' => Reservation::where('status', 'pending')->count(),
            'collected' => $collected->count(),
            'cancelled' => Reservation::where('status', 'cancelled')->count(),
            'expired' => Reservation::where('status', 'expired')->count(),
        ];

        $recentAuditLogs = AuditLog::with('user')->latest()->take(8)->get();

        return view('admin.dashboard', [
            'totalUsers' => User::count(),
            'totalBuyers' => User::where('role', 'buyer')->count(),
            'totalSellers' => User::where('role', 'seller')->count(),
            'totalStores' => Store::count(),
            'activeListings' => Listing::where('status', 'active')->count(),
            'totalReservations' => Reservation::count(),
            'collectedReservations' => $statusCounts['collected'],
            'cancelledReservations' => $statusCounts['cancelled'],
            'expiredReservations' => $statusCounts['expired'],
            'pendingReservations' => $statusCounts['pending'],
            'totalConsumerSavings' => round($totalConsumerSavings, 2),
            'statusCounts' => $statusCounts,
            'recentAuditLogs' => $recentAuditLogs,
        ]);
    }
}
