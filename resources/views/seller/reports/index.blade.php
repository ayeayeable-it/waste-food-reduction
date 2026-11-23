@extends('layouts.app')

@section('title', 'Seller Reports — RescueBite')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-brand-dark">Store Collection &amp; Impact Reports</h1>
                <p class="text-sm text-slate-500">Reporting period: {{ $report['period_start'] }} to {{ $report['period_end'] }}</p>
            </div>
            <form method="GET" class="flex flex-wrap items-end gap-2">
                <div>
                    <label class="block text-[11px] font-medium text-slate-500">Start Date</label>
                    <input type="date" name="start_date" value="{{ $report['period_start'] }}" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm bg-white">
                </div>
                <div>
                    <label class="block text-[11px] font-medium text-slate-500">End Date</label>
                    <input type="date" name="end_date" value="{{ $report['period_end'] }}" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm bg-white">
                </div>
                <button type="submit" class="rounded-lg bg-brand-primary px-4 py-2 text-xs font-semibold text-white hover:bg-brand-dark">Update</button>
            </form>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="rounded-xl bg-white p-5 border border-slate-200 shadow-sm">
                <p class="text-xs uppercase text-slate-500">Collected Units</p>
                <p class="mt-2 text-3xl font-bold text-brand-dark">{{ $report['total_collected_units'] }}</p>
                <p class="text-xs text-slate-500 mt-1">{{ $report['collected_count'] }} completed order(s)</p>
            </div>
            <div class="rounded-xl bg-white p-5 border border-slate-200 shadow-sm">
                <p class="text-xs uppercase text-slate-500">Collected Revenue</p>
                <p class="mt-2 text-3xl font-bold text-brand-primary">${{ number_format($report['total_collected_revenue'], 2) }}</p>
            </div>
            <div class="rounded-xl bg-white p-5 border border-slate-200 shadow-sm">
                <p class="text-xs uppercase text-slate-500">Consumer Savings Generated</p>
                <p class="mt-2 text-3xl font-bold text-brand-teal">${{ number_format($report['total_consumer_savings'], 2) }}</p>
                <p class="text-[11px] text-slate-400 mt-1">Collected reservations only</p>
            </div>
            <div class="rounded-xl bg-white p-5 border border-slate-200 shadow-sm">
                <p class="text-xs uppercase text-slate-500">Collection Success Rate</p>
                <p class="mt-2 text-3xl font-bold text-slate-800">{{ $report['collection_rate'] }}%</p>
                <p class="text-[11px] text-slate-400 mt-1">{{ $report['collected_count'] }} / {{ $report['eligible_denominator'] }} finalized</p>
            </div>
        </div>

        <div class="rounded-2xl bg-white p-6 border border-slate-200 shadow-sm">
            <h2 class="text-base font-bold text-slate-800 mb-4">Reservation Outcomes Trend</h2>
            <canvas id="seller-trend-chart" height="90"></canvas>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const trend = @json($report['daily_trend']);
            new Chart(document.getElementById('seller-trend-chart'), {
                type: 'bar',
                data: {
                    labels: trend.map(d => d.date),
                    datasets: [
                        { label: 'Collected', data: trend.map(d => d.collected), backgroundColor: '#16A34A' },
                        { label: 'Expired', data: trend.map(d => d.expired), backgroundColor: '#94A3B8' },
                        { label: 'Cancelled', data: trend.map(d => d.cancelled), backgroundColor: '#F59E0B' }
                    ]
                },
                options: { responsive: true, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
            });
        });
    </script>
@endpush
