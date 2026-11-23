@extends('layouts.app')

@section('title', 'Administrator Dashboard — RescueBite')

@section('content')
    <div class="space-y-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-brand-dark">Administrator Dashboard</h1>
                <p class="text-sm text-slate-500">Platform-wide oversight, governance, and analytics.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.notifications.index') }}" class="rounded-lg border border-brand-teal px-3 py-2 text-xs font-semibold text-brand-teal hover:bg-teal-50">
                    Manage Broadcasts
                </a>
                <a href="{{ route('admin.reports.index') }}" class="rounded-lg bg-brand-primary px-4 py-2 text-xs font-semibold text-white hover:bg-brand-dark">
                    Full System Reports
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="rounded-xl bg-white p-5 border border-slate-200 shadow-sm">
                <p class="text-xs font-medium text-slate-500 uppercase">Total Users</p>
                <p class="mt-2 text-2xl font-bold text-slate-800">{{ $totalUsers }}</p>
                <p class="text-xs text-slate-500 mt-1">{{ $totalBuyers }} buyers · {{ $totalSellers }} sellers</p>
            </div>
            <div class="rounded-xl bg-white p-5 border border-slate-200 shadow-sm">
                <p class="text-xs font-medium text-slate-500 uppercase">Total Stores</p>
                <p class="mt-2 text-2xl font-bold text-brand-dark">{{ $totalStores }}</p>
            </div>
            <div class="rounded-xl bg-white p-5 border border-slate-200 shadow-sm">
                <p class="text-xs font-medium text-slate-500 uppercase">Active Listings</p>
                <p class="mt-2 text-2xl font-bold text-brand-primary">{{ $activeListings }}</p>
            </div>
            <div class="rounded-xl bg-white p-5 border border-slate-200 shadow-sm">
                <p class="text-xs font-medium text-slate-500 uppercase">Reservations</p>
                <p class="mt-2 text-2xl font-bold text-slate-800">{{ $totalReservations }}</p>
                <p class="text-xs text-slate-500 mt-1">{{ $collectedReservations }} collected · {{ $cancelledReservations }} cancelled · {{ $expiredReservations }} expired</p>
            </div>
            <div class="rounded-xl bg-white p-5 border border-slate-200 shadow-sm">
                <p class="text-xs font-medium text-slate-500 uppercase">Consumer Savings</p>
                <p class="mt-2 text-2xl font-bold text-brand-teal">${{ number_format($totalConsumerSavings, 2) }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="rounded-2xl bg-white p-6 border border-slate-200 shadow-sm">
                <h2 class="text-base font-bold text-slate-800 mb-4">Reservation Status Breakdown</h2>
                <canvas id="admin-status-chart" height="220"></canvas>
            </div>

            <div class="lg:col-span-2 rounded-2xl bg-white p-6 border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-base font-bold text-slate-800">Recent Audit Activity</h2>
                    <a href="{{ route('admin.audit-logs.index') }}" class="text-xs font-semibold text-brand-primary hover:underline">View all logs &rarr;</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="border-b border-slate-200 uppercase text-slate-500">
                            <tr>
                                <th class="py-2 pr-3">Time</th>
                                <th class="py-2 pr-3">Actor</th>
                                <th class="py-2 pr-3">Action</th>
                                <th class="py-2">Target</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($recentAuditLogs as $log)
                                <tr>
                                    <td class="py-2 pr-3 text-slate-500">{{ $log->created_at->format('M d, H:i') }}</td>
                                    <td class="py-2 pr-3 font-medium text-slate-800">{{ $log->user?->name ?? 'System' }}</td>
                                    <td class="py-2 pr-3 font-mono text-brand-dark">{{ $log->action }}</td>
                                    <td class="py-2 text-slate-500">{{ class_basename($log->target_type ?? '') }} #{{ $log->target_id }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-4 text-center text-slate-500">No audit logs recorded yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const counts = @json($statusCounts);
            new Chart(document.getElementById('admin-status-chart'), {
                type: 'doughnut',
                data: {
                    labels: ['Collected', 'Pending', 'Cancelled', 'Expired'],
                    datasets: [{
                        data: [counts.collected, counts.pending, counts.cancelled, counts.expired],
                        backgroundColor: ['#16A34A', '#F59E0B', '#EF4444', '#94A3B8']
                    }]
                },
                options: { responsive: true }
            });
        });
    </script>
@endpush
