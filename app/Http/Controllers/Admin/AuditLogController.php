<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = AuditLog::with('user')->latest();

        if ($request->filled('action')) {
            $action = trim((string) $request->input('action'));
            $query->where('action', 'like', "%{$action}%");
        }

        $logs = $query->paginate(20)->withQueryString();

        return view('admin.audit-logs.index', compact('logs'));
    }
}
