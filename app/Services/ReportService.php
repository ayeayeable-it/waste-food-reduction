<?php

namespace App\Services;

use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ReportService
{
    /**
     * Optional illustrative estimation constants (not verified measurements).
     * Clearly disclosed in the reporting UI with assumptions and limitations.
     */
    public const ESTIMATED_KG_PER_UNIT = 0.50;
    public const ESTIMATED_CO2E_KG_PER_KG_FOOD = 2.50;

    /**
     * Build comprehensive reservation, savings, and collection analytics.
     *
     * @param  Carbon  $startDate
     * @param  Carbon  $endDate
     * @param  array<int>|null  $storeIds  Limit to specific stores (for seller reports) or null for platform-wide.
     */
    public function generateSummary(Carbon $startDate, Carbon $endDate, ?array $storeIds = null): array
    {
        $baseQuery = Reservation::query()
            ->whereBetween('reserved_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()]);

        if ($storeIds !== null) {
            $baseQuery->whereHas('listing', fn (Builder $q) => $q->whereIn('store_id', $storeIds));
        }

        $reservations = $baseQuery->with(['listing.category', 'listing.store'])->get();

        $collected = $reservations->where('status', 'collected');
        $pending = $reservations->where('status', 'pending');
        $cancelled = $reservations->where('status', 'cancelled');
        $expired = $reservations->where('status', 'expired');

        // Primary consumer savings: strictly from successfully collected reservations
        $totalConsumerSavings = round($collected->sum(
            fn (Reservation $r) => (float) $r->original_total_price - (float) $r->total_discounted_price
        ), 2);

        $totalCollectedRevenue = round($collected->sum(
            fn (Reservation $r) => (float) $r->total_discounted_price
        ), 2);

        $totalCollectedUnits = (int) $collected->sum('quantity');

        // Denominator for Collection Success Rate:
        // Finalized/eligible reservations in the reporting period (collected + expired + cancelled)
        $eligibleDenominator = $collected->count() + $expired->count() + $cancelled->count();
        $collectionRate = $eligibleDenominator > 0
            ? round(($collected->count() / $eligibleDenominator) * 100, 1)
            : 0.0;

        // Optional illustrative environmental estimates (clearly caveated in UI)
        $estimatedMassKg = round($totalCollectedUnits * self::ESTIMATED_KG_PER_UNIT, 2);
        $estimatedCo2eKg = round($estimatedMassKg * self::ESTIMATED_CO2E_KG_PER_KG_FOOD, 2);

        // Breakdown by category (collected units & savings)
        $byCategory = $collected
            ->groupBy(fn (Reservation $r) => $r->listing?->category?->name ?? 'Uncategorized')
            ->map(function (Collection $group, string $categoryName) {
                return [
                    'category' => $categoryName,
                    'collected_orders' => $group->count(),
                    'collected_units' => (int) $group->sum('quantity'),
                    'savings' => round($group->sum(
                        fn (Reservation $r) => (float) $r->original_total_price - (float) $r->total_discounted_price
                    ), 2),
                ];
            })
            ->sortByDesc('collected_units')
            ->values();

        // Daily trend for Chart.js
        $dailyTrend = $reservations
            ->groupBy(fn (Reservation $r) => $r->reserved_at->format('Y-m-d'))
            ->sortKeys()
            ->map(function (Collection $dayGroup, string $date) {
                return [
                    'date' => $date,
                    'collected' => $dayGroup->where('status', 'collected')->count(),
                    'expired' => $dayGroup->where('status', 'expired')->count(),
                    'cancelled' => $dayGroup->where('status', 'cancelled')->count(),
                    'pending' => $dayGroup->where('status', 'pending')->count(),
                ];
            })
            ->values();

        return [
            'period_start' => $startDate->toDateString(),
            'period_end' => $endDate->toDateString(),
            'total_reservations' => $reservations->count(),
            'collected_count' => $collected->count(),
            'pending_count' => $pending->count(),
            'cancelled_count' => $cancelled->count(),
            'expired_count' => $expired->count(),
            'eligible_denominator' => $eligibleDenominator,
            'collection_rate' => $collectionRate,
            'total_collected_units' => $totalCollectedUnits,
            'total_collected_revenue' => $totalCollectedRevenue,
            'total_consumer_savings' => $totalConsumerSavings,
            'estimated_mass_kg' => $estimatedMassKg,
            'estimated_co2e_kg' => $estimatedCo2eKg,
            'by_category' => $byCategory,
            'daily_trend' => $dailyTrend,
        ];
    }
}
