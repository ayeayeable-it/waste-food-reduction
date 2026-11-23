<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ListingController extends Controller
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly NotificationService $notificationService
    ) {}

    public function index(Request $request): View
    {
        $query = Listing::with(['store.seller', 'category'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where('food_name', 'like', "%{$search}%");
        }

        $listings = $query->paginate(15)->withQueryString();

        return view('admin.listings.index', compact('listings'));
    }

    public function close(Listing $listing): RedirectResponse
    {
        if ($listing->status === 'closed') {
            return back()->with('warning', 'Listing is already closed.');
        }

        $listing->update(['status' => 'closed']);
        $listing->load('store');

        $this->notificationService->send(
            userId: $listing->store->user_id,
            type: 'listing_closed',
            title: 'Listing Closed by Administrator',
            message: "Your listing \"{$listing->food_name}\" was closed by a platform administrator.",
            relatedType: Listing::class,
            relatedId: $listing->id
        );

        $this->auditLogService->log(
            action: 'admin_listing_closed',
            targetType: Listing::class,
            targetId: $listing->id,
            details: ['food_name' => $listing->food_name, 'store_id' => $listing->store_id]
        );

        return back()->with('success', "Listing \"{$listing->food_name}\" has been closed.");
    }
}
