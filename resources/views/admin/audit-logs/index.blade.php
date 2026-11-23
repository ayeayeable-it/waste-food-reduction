@extends('layouts.app')

@section('title', 'System Audit Logs — RescueBite Admin')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-brand-dark">System Audit Logs</h1>
            <p class="text-sm text-slate-500">Immutable record of authentication, reservation, and administrative actions.</p>
        </div>

        <form method="GET" class="flex gap-3">
            <input type="text" name="action" value="{{ request('action') }}" placeholder="Filter by action (e.g., admin_, reservation_)..." class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm bg-white w-80">
            <button type="submit" class="rounded-lg bg-brand-teal px-4 py-1.5 text-sm font-semibold text-white">Filter</button>
            <a href="{{ route('admin.audit-logs.index') }}" class="text-xs text-slate-500 py-2 hover:underline">Reset</a>
        </form>

        <div class="rounded-xl bg-white border border-slate-200 shadow-sm overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Timestamp</th>
                        <th class="px-4 py-3">User</th>
                        <th class="px-4 py-3">Action</th>
                        <th class="px-4 py-3">Target</th>
                        <th class="px-4 py-3">Details</th>
                        <th class="px-4 py-3">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($logs as $log)
                        <tr>
                            <td class="px-4 py-3 text-slate-500 whitespace-nowrap">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                            <td class="px-4 py-3 font-medium text-slate-800">{{ $log->user?->email ?? 'System Scheduler' }}</td>
                            <td class="px-4 py-3 font-mono font-semibold text-brand-dark">{{ $log->action }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ class_basename($log->target_type ?? '') }} {{ $log->target_id ? '#' . $log->target_id : '' }}</td>
                            <td class="px-4 py-3 font-mono text-[11px] text-slate-500 max-w-xs truncate">{{ json_encode($log->details) }}</td>
                            <td class="px-4 py-3 text-slate-400">{{ $log->ip_address ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div>{{ $logs->links() }}</div>
    </div>
@endsection
