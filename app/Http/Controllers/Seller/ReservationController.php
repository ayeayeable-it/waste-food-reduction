<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
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
        $storeIds = $request->user()->stores()->pluck('id');

        $query = Reservation::whereHas('listing', fn ($q) => $q->whereIn('store_id', $storeIds))
            ->with(['buyer', 'listing.store'])
            ->latest('reserved_at');

        if ($request->filled('reference')) {
            $ref = trim((string) $request->input('reference'));
            $query->where('reservation_reference', 'like', "%{$ref}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $reservations = $query->paginate(12)->withQueryString();

        return view('seller.reservations.index', compact('reservations'));
    }

    public function confirmCollection(Request $request): RedirectResponse
    {
        $request->validate([
            'reservation_reference' => ['required', 'string', 'max:32'],
        ]);

        try {
            $reservation = $this->reservationService->confirmCollection(
                seller: $request->user(),
                reference: (string) $request->input('reservation_reference')
            );

            return redirect()->route('seller.reservations.index')
                ->with('success', "Collection confirmed for {$reservation->reservation_reference} ({$reservation->quantity}x {$reservation->listing->food_name}).");
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}
