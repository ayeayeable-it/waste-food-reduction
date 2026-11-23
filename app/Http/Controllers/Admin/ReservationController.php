<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReservationController extends Controller
{
    public function index(Request $request): View
    {
        $query = Reservation::with(['buyer', 'listing.store'])->latest('reserved_at');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('reference')) {
            $ref = trim((string) $request->input('reference'));
            $query->where('reservation_reference', 'like', "%{$ref}%");
        }

        $reservations = $query->paginate(15)->withQueryString();

        return view('admin.reservations.index', compact('reservations'));
    }
}
