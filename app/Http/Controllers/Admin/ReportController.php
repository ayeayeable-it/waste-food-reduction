<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditLogService;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        private readonly ReportService $reportService,
        private readonly AuditLogService $auditLogService
    ) {}

    public function index(Request $request): View
    {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'))
            : now()->subDays(30);

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'))
            : now();

        $report = $this->reportService->generateSummary($startDate, $endDate);

        return view('admin.reports.index', compact('report'));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'))
            : now()->subDays(30);

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'))
            : now();

        $report = $this->reportService->generateSummary($startDate, $endDate);

        $this->auditLogService->log(
            action: 'admin_report_exported',
            targetType: 'Report',
            targetId: null,
            details: ['start' => $report['period_start'], 'end' => $report['period_end']]
        );

        $filename = "rescuebite-report-{$report['period_start']}-to-{$report['period_end']}.csv";

        return response()->streamDownload(function () use ($report) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Metric', 'Value']);
            fputcsv($handle, ['Reporting Period', "{$report['period_start']} to {$report['period_end']}"]);
            fputcsv($handle, ['Total Reservations', $report['total_reservations']]);
            fputcsv($handle, ['Collected Reservations', $report['collected_count']]);
            fputcsv($handle, ['Cancelled Reservations', $report['cancelled_count']]);
            fputcsv($handle, ['Expired Reservations', $report['expired_count']]);
            fputcsv($handle, ['Collection Success Rate (%)', $report['collection_rate']]);
            fputcsv($handle, ['Total Collected Units', $report['total_collected_units']]);
            fputcsv($handle, ['Total Consumer Savings (Collected Only)', number_format($report['total_consumer_savings'], 2)]);
            fputcsv($handle, ['Illustrative Est. Mass Collected (kg - Unverified)', $report['estimated_mass_kg']]);
            fputcsv($handle, ['Illustrative Est. CO2e (kg - Unverified)', $report['estimated_co2e_kg']]);
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
