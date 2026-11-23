<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(
        private readonly NotificationService $notificationService,
        private readonly AuditLogService $auditLogService
    ) {}

    public function index(): View
    {
        $notifications = Notification::with('user')->latest()->paginate(15);

        return view('admin.notifications.index', compact('notifications'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'target_role' => ['required', Rule::in(['all', 'buyer', 'seller'])],
            'type' => ['required', Rule::in(['system_announcement', 'collection_reminder'])],
            'title' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $usersQuery = User::where('is_active', true);
        if ($validated['target_role'] !== 'all') {
            $usersQuery->where('role', $validated['target_role']);
        }

        $recipients = $usersQuery->pluck('id');
        foreach ($recipients as $userId) {
            $this->notificationService->send(
                userId: $userId,
                type: $validated['type'],
                title: $validated['title'],
                message: $validated['message']
            );
        }

        $this->auditLogService->log(
            action: 'admin_notification_broadcast',
            targetType: Notification::class,
            targetId: null,
            details: [
                'target_role' => $validated['target_role'],
                'recipient_count' => $recipients->count(),
                'title' => $validated['title'],
            ]
        );

        return back()->with('success', "Notification sent to {$recipients->count()} user(s).");
    }
}
