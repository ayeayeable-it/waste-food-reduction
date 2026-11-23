<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(
        private readonly ReportService $reportService
    ) {}

    public function index(Request $request): View
    {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'))
            : now()->subDays(30);

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'))
            : now();

        $storeIds = $request->user()->stores()->pluck('id')->all();
        $report = $this->reportService->generateSummary($startDate, $endDate, $storeIds);

        return view('seller.reports.index', compact('report'));
    }
}
