<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
        $query = Store::with('seller')->withCount('listings')->latest();

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $stores = $query->paginate(15)->withQueryString();

        return view('admin.stores.index', compact('stores'));
    }

    public function toggleStatus(Store $store): RedirectResponse
    {
        $newStatus = ! $store->is_active;
        $store->update(['is_active' => $newStatus]);

        $this->auditLogService->log(
            action: $newStatus ? 'admin_store_activated' : 'admin_store_deactivated',
            targetType: Store::class,
            targetId: $store->id,
            details: ['store_name' => $store->name, 'is_active' => $newStatus]
        );

        return back()->with('success', "Store \"{$store->name}\" has been " . ($newStatus ? 'activated' : 'deactivated') . '.');
    }
}
