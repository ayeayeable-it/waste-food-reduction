@extends('layouts.app')

@section('title', 'System Reports & Analytics — RescueBite Admin')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-brand-dark">System Reports &amp; Analytics</h1>
                <p class="text-sm text-slate-500">Reporting period: <strong>{{ $report['period_start'] }}</strong> to <strong>{{ $report['period_end'] }}</strong></p>
            </div>
            <div class="flex flex-wrap items-end gap-2">
                <form method="GET" class="flex flex-wrap items-end gap-2">
                    <div>
                        <label class="block text-[11px] font-medium text-slate-500">Start Date</label>
                        <input type="date" name="start_date" value="{{ $report['period_start'] }}" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm bg-white">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-500">End Date</label>
                        <input type="date" name="end_date" value="{{ $report['period_end'] }}" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm bg-white">
                    </div>
                    <button type="submit" class="rounded-lg bg-brand-teal px-4 py-2 text-xs font-semibold text-white">Apply</button>
                </form>
                <a href="{{ route('admin.reports.export', ['start_date' => $report['period_start'], 'end_date' => $report['period_end']]) }}"
                   class="rounded-lg bg-brand-primary px-4 py-2 text-xs font-semibold text-white hover:bg-brand-dark">
                    Export CSV
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="rounded-xl bg-white p-5 border border-slate-200 shadow-sm">
                <p class="text-xs uppercase text-slate-500">Consumer Savings (Collected)</p>
                <p class="mt-2 text-3xl font-bold text-brand-primary">${{ number_format($report['total_consumer_savings'], 2) }}</p>
                <p class="text-[11px] text-slate-400 mt-1">Sum of collected qty &times; (orig &minus; disc)</p>
            </div>
            <div class="rounded-xl bg-white p-5 border border-slate-200 shadow-sm">
                <p class="text-xs uppercase text-slate-500">Collection Success Rate</p>
                <p class="mt-2 text-3xl font-bold text-brand-dark">{{ $report['collection_rate'] }}%</p>
                <p class="text-[11px] text-slate-400 mt-1">{{ $report['collected_count'] }} collected / {{ $report['eligible_denominator'] }} finalized</p>
            </div>
            <div class="rounded-xl bg-white p-5 border border-slate-200 shadow-sm">
                <p class="text-xs uppercase text-slate-500">Total Collected Units</p>
                <p class="mt-2 text-3xl font-bold text-brand-teal">{{ $report['total_collected_units'] }}</p>
                <p class="text-[11px] text-slate-400 mt-1">From {{ $report['collected_count'] }} collected orders</p>
            </div>
            <div class="rounded-xl bg-white p-5 border border-slate-200 shadow-sm">
                <p class="text-xs uppercase text-slate-500">Illustrative Est. Mass / CO2e</p>
                <p class="mt-2 text-xl font-bold text-slate-700">{{ $report['estimated_mass_kg'] }} kg · {{ $report['estimated_co2e_kg'] }} kg CO2e</p>
                <p class="text-[11px] text-amber-700 mt-1">Unverified estimate (see methodology below)</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="rounded-2xl bg-white p-6 border border-slate-200 shadow-sm">
                <h2 class="text-base font-bold text-slate-800 mb-4">Daily Reservation Outcomes</h2>
                <canvas id="admin-daily-chart" height="140"></canvas>
            </div>
            <div class="rounded-2xl bg-white p-6 border border-slate-200 shadow-sm">
                <h2 class="text-base font-bold text-slate-800 mb-4">Collected Units by Category</h2>
                <canvas id="admin-category-chart" height="140"></canvas>
            </div>
        </div>

        <div class="rounded-2xl bg-amber-50 border border-amber-200 p-5 text-xs text-amber-900 space-y-1.5">
            <p class="font-bold uppercase tracking-wider">Methodology, Assumptions &amp; Limitations</p>
            <p><strong>Consumer Savings:</strong> Calculated strictly from reservations with status <code>collected</code> using historical snapshot prices: <code>Sum of (Original Unit Price &minus; Discounted Unit Price) &times; Quantity</code>.</p>
            <p><strong>Collection Success Rate:</strong> Defined as <code>Collected / (Collected + Expired + Cancelled) &times; 100</code> for reservations created within the selected period (excluding currently active <code>pending</code> reservations).</p>
            <p><strong>Environmental Estimates Disclaimer:</strong> Food mass ({{ \App\Services\ReportService::ESTIMATED_KG_PER_UNIT }} kg/unit assumed) and associated emissions ({{ \App\Services\ReportService::ESTIMATED_CO2E_KG_PER_KG_FOOD }} kg CO2e/kg food reference factor) are illustrative estimates only. RescueBite does not claim verified food-waste reduction or verified emissions avoidance.</p>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const daily = @json($report['daily_trend']);
            const categories = @json($report['by_category']);

            new Chart(document.getElementById('admin-daily-chart'), {
                type: 'bar',
                data: {
                    labels: daily.map(d => d.date),
                    datasets: [
                        { label: 'Collected', data: daily.map(d => d.collected), backgroundColor: '#16A34A' },
                        { label: 'Cancelled', data: daily.map(d => d.cancelled), backgroundColor: '#F59E0B' },
                        { label: 'Expired', data: daily.map(d => d.expired), backgroundColor: '#94A3B8' }
                    ]
                },
                options: { responsive: true, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
            });

            new Chart(document.getElementById('admin-category-chart'), {
                type: 'bar',
                data: {
                    labels: categories.map(c => c.category),
                    datasets: [{
                        label: 'Collected Units',
                        data: categories.map(c => c.collected_units),
                        backgroundColor: '#0F766E'
                    }]
                },
                options: { responsive: true, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
            });
        });
    </script>
@endpush
