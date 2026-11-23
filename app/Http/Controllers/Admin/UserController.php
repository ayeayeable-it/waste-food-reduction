<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        private readonly AuditLogService $auditLogService
    ) {}

    public function index(Request $request): View
    {
        $query = User::query()->latest();

        if ($request->filled('role')) {
            $query->where('role', $request->string('role'));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'You cannot deactivate your own administrator account.');
        }

        $newStatus = ! $user->is_active;
        $user->update(['is_active' => $newStatus]);

        $this->auditLogService->log(
            action: $newStatus ? 'admin_user_activated' : 'admin_user_deactivated',
            targetType: User::class,
            targetId: $user->id,
            details: ['email' => $user->email, 'role' => $user->role, 'is_active' => $newStatus]
        );

        return back()->with('success', "User {$user->name} has been " . ($newStatus ? 'activated' : 'deactivated') . '.');
    }
}
