<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditLogService
{
    /**
     * Record a security or business audit event.
     */
    public function log(
        string $action,
        ?string $targetType = null,
        ?int $targetId = null,
        array $details = [],
        ?int $userId = null
    ): AuditLog {
        return AuditLog::create([
            'user_id' => $userId ?? Auth::id(),
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'details' => $details,
            'ip_address' => request()?->ip(),
        ]);
    }
}
