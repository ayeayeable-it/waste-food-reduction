<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Buyer\StoreReservationRequest;
use App\Models\Listing;
use App\Models\Reservation;
use App\Services\ReservationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class ReservationController extends Controller
{
    public function __construct(
        private readonly ReservationService $reservationService
    ) {}

    public function index(Request $request): View
    {
        $buyer = $request->user();

        $query = $buyer->reservations()
            ->with(['listing.store', 'listing.category'])
            ->latest('reserved_at');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $reservations = $query->paginate(10)->withQueryString();

        $collected = $buyer->reservations()->where('status', 'collected')->get();
        $totalSavings = $collected->sum(
            fn (Reservation $r) => (float) $r->original_total_price - (float) $r->total_discounted_price
        );

        return view('buyer.reservations.index', [
            'reservations' => $reservations,
            'totalCollectedUnits' => $collected->sum('quantity'),
            'totalSavings' => round($totalSavings, 2),
        ]);
    }

    public function store(StoreReservationRequest $request, Listing $listing): RedirectResponse
    {
        try {
            $reservation = $this->reservationService->createReservation(
                buyer: $request->user(),
                listingId: $listing->id,
                quantity: (int) $request->validated('quantity'),
                acknowledgements: $request->only([
                    'acknowledged_product_info',
                    'acknowledged_expiry',
                    'acknowledged_allergens',
                    'acknowledged_storage',
                    'acknowledged_collection',
                    'disclaimer_accepted',
                ])
            );

            return redirect()->route('buyer.reservations.show', $reservation)
                ->with('success', "Reservation confirmed! Reference: {$reservation->reservation_reference}");
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(Request $request, Reservation $reservation): View
    {
        if ($reservation->buyer_id !== $request->user()->id) {
            abort(403, 'You are not authorized to view this reservation.');
        }

        $reservation->load(['listing.store', 'listing.category', 'acknowledgement']);

        return view('buyer.reservations.show', compact('reservation'));
    }

    public function cancel(Request $request, Reservation $reservation): RedirectResponse
    {
        if ($reservation->buyer_id !== $request->user()->id) {
            abort(403, 'You are not authorized to cancel this reservation.');
        }

        try {
            $this->reservationService->cancelReservation($request->user(), $reservation->id);

            return redirect()->route('buyer.reservations.show', $reservation)
                ->with('success', 'Your reservation has been cancelled.');
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}