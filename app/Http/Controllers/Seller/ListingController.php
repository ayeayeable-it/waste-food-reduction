<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Seller\ListingRequest;
use App\Models\Category;
use App\Models\Listing;
use App\Models\StockMovement;
use App\Services\AuditLogService;
use App\Services\ImageUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ListingController extends Controller
{
    public function __construct(
        private readonly ImageUploadService $imageUploadService,
        private readonly AuditLogService $auditLogService
    ) {}

    public function index(Request $request): View
    {
        $storeIds = $request->user()->stores()->pluck('id');

        $query = Listing::whereIn('store_id', $storeIds)
            ->with(['store', 'category']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('store_id')) {
            $query->where('store_id', $request->integer('store_id'));
        }

        $listings = $query->latest()->paginate(10)->withQueryString();
        $stores = $request->user()->stores()->orderBy('name')->get();

        return view('seller.listings.index', compact('listings', 'stores'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        $stores = $request->user()->stores()->where('is_active', true)->orderBy('name')->get();

        if ($stores->isEmpty()) {
            return redirect()->route('seller.stores.create')
                ->with('warning', 'Please register an active store before publishing a food listing.');
        }

        $categories = Category::orderBy('name')->get();

        return view('seller.listings.create', compact('stores', 'categories'));
    }

    public function store(ListingRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $this->imageUploadService->uploadListingImage($request->file('image'));
        }

        $listing = DB::transaction(function () use ($validated, $imagePath) {
            $listing = Listing::create([
                'store_id' => $validated['store_id'],
                'category_id' => $validated['category_id'],
                'food_name' => $validated['food_name'],
                'description' => $validated['description'],
                'image_path' => $imagePath,
                'original_price' => $validated['original_price'],
                'discounted_price' => $validated['discounted_price'],
                'available_quantity' => $validated['available_quantity'],
                'expiry_label' => $validated['expiry_label'],
                'storage_instructions' => $validated['storage_instructions'],
                'allergen_info' => $validated['allergen_info'],
                'collection_deadline' => $validated['collection_deadline'],
                'status' => 'active',
            ]);

            StockMovement::create([
                'listing_id' => $listing->id,
                'movement_type' => 'manual_adjustment',
                'quantity_change' => $listing->available_quantity,
                'idempotency_key' => 'init_' . $listing->id . '_' . Str::uuid()->toString(),
                'notes' => 'Initial stock set by seller on listing creation.',
            ]);

            return $listing;
        });

        $this->auditLogService->log(
            action: 'listing_created',
            targetType: Listing::class,
            targetId: $listing->id,
            details: [
                'food_name' => $listing->food_name,
                'quantity' => $listing->available_quantity,
                'discounted_price' => $listing->discounted_price,
            ]
        );

        return redirect()->route('seller.listings.index')
            ->with('success', 'Surplus food listing "' . $listing->food_name . '" is now live.');
    }

    public function edit(Request $request, Listing $listing): View
    {
        $this->authorizeOwnership($request, $listing);

        $stores = $request->user()->stores()->orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        return view('seller.listings.edit', compact('listing', 'stores', 'categories'));
    }

    public function update(ListingRequest $request, Listing $listing): RedirectResponse
    {
        $this->authorizeOwnership($request, $listing);
        $validated = $request->validated();

        $imagePath = $listing->image_path;
        if ($request->hasFile('image')) {
            $imagePath = $this->imageUploadService->uploadListingImage($request->file('image'), $listing->image_path);
        }

        DB::transaction(function () use ($listing, $validated, $imagePath) {
            $lockedListing = Listing::where('id', $listing->id)->lockForUpdate()->firstOrFail();
            $oldQuantity = $lockedListing->available_quantity;
            $newQuantity = (int) $validated['available_quantity'];
            $delta = $newQuantity - $oldQuantity;

            $newStatus = $lockedListing->status;
            if ($newStatus !== 'closed') {
                $newStatus = $newQuantity === 0 ? 'sold_out' : 'active';
            }

            $lockedListing->update([
                'store_id' => $validated['store_id'],
                'category_id' => $validated['category_id'],
                'food_name' => $validated['food_name'],
                'description' => $validated['description'],
                'image_path' => $imagePath,
                'original_price' => $validated['original_price'],
                'discounted_price' => $validated['discounted_price'],
                'available_quantity' => $newQuantity,
                'expiry_label' => $validated['expiry_label'],
                'storage_instructions' => $validated['storage_instructions'],
                'allergen_info' => $validated['allergen_info'],
                'collection_deadline' => $validated['collection_deadline'],
                'status' => $newStatus,
            ]);

            if ($delta !== 0) {
                StockMovement::create([
                    'listing_id' => $lockedListing->id,
                    'movement_type' => 'manual_adjustment',
                    'quantity_change' => $delta,
                    'idempotency_key' => 'adj_' . $lockedListing->id . '_' . Str::uuid()->toString(),
                    'notes' => "Manual stock update by seller ({$oldQuantity} -> {$newQuantity}).",
                ]);
            }
        });

        $this->auditLogService->log(
            action: 'listing_updated',
            targetType: Listing::class,
            targetId: $listing->id,
            details: ['food_name' => $validated['food_name']]
        );

        return redirect()->route('seller.listings.index')
            ->with('success', 'Listing "' . $validated['food_name'] . '" has been updated.');
    }

    public function close(Request $request, Listing $listing): RedirectResponse
    {
        $this->authorizeOwnership($request, $listing);

        if ($listing->status === 'closed') {
            return back()->with('warning', 'This listing is already closed.');
        }

        $listing->update(['status' => 'closed']);

        $this->auditLogService->log(
            action: 'listing_closed',
            targetType: Listing::class,
            targetId: $listing->id,
            details: ['food_name' => $listing->food_name]
        );

        return redirect()->route('seller.listings.index')
            ->with('success', 'Listing "' . $listing->food_name . '" has been closed and will no longer accept reservations.');
    }

    private function authorizeOwnership(Request $request, Listing $listing): void
    {
        if ($listing->store->user_id !== $request->user()->id) {
            abort(403, 'You are not authorized to manage this food listing.');
        }
    }
}
