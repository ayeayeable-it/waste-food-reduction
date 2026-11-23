<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Seller\StoreRequest;
use App\Models\Store;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StoreController extends Controller
{
    public function __construct(
        private readonly AuditLogService $auditLogService
    ) {}

    public function index(Request $request): View
    {
        $stores = $request->user()
            ->stores()
            ->withCount('listings')
            ->latest()
            ->paginate(10);

        return view('seller.stores.index', compact('stores'));
    }

    public function create(): View
    {
        return view('seller.stores.create');
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $store = $request->user()->stores()->create([
            'name' => $validated['name'],
            'address' => $validated['address'],
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'phone' => $validated['phone'] ?? null,
            'collection_instructions' => $validated['collection_instructions'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        $this->auditLogService->log(
            action: 'store_created',
            targetType: Store::class,
            targetId: $store->id,
            details: ['name' => $store->name]
        );

        return redirect()->route('seller.stores.index')
            ->with('success', 'Store "' . $store->name . '" has been registered.');
    }

    public function edit(Request $request, Store $store): View
    {
        $this->authorizeOwnership($request, $store);

        return view('seller.stores.edit', compact('store'));
    }

    public function update(StoreRequest $request, Store $store): RedirectResponse
    {
        $this->authorizeOwnership($request, $store);
        $validated = $request->validated();

        $store->update([
            'name' => $validated['name'],
            'address' => $validated['address'],
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'phone' => $validated['phone'] ?? null,
            'collection_instructions' => $validated['collection_instructions'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->auditLogService->log(
            action: 'store_updated',
            targetType: Store::class,
            targetId: $store->id,
            details: ['name' => $store->name, 'is_active' => $store->is_active]
        );

        return redirect()->route('seller.stores.index')
            ->with('success', 'Store "' . $store->name . '" has been updated.');
    }

    private function authorizeOwnership(Request $request, Store $store): void
    {
        if ($store->user_id !== $request->user()->id) {
            abort(403, 'You are not authorized to manage this store.');
        }
    }
}
