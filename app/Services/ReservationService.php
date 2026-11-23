<?php

namespace App\Services;

use App\Models\Acknowledgement;
use App\Models\Listing;
use App\Models\Reservation;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class ReservationService
{
    public function __construct(
        private readonly NotificationService $notificationService,
        private readonly AuditLogService $auditLogService
    ) {}

    /**
     * Create a reservation with pessimistic row locking (SELECT ... FOR UPDATE)
     * to prevent overselling under concurrent requests.
     */
    public function createReservation(User $buyer, int $listingId, int $quantity, array $acknowledgements): Reservation
    {
        return DB::transaction(function () use ($buyer, $listingId, $quantity, $acknowledgements) {
            /** @var Listing|null $listing */
            $listing = Listing::with('store')
                ->where('id', $listingId)
                ->lockForUpdate()
                ->first();

            if (! $listing) {
                throw new RuntimeException('The requested food listing does not exist.');
            }

            if ($listing->status !== 'active' || ! $listing->store?->is_active) {
                throw new RuntimeException('This food listing is no longer active.');
            }

            if ($listing->collection_deadline->isPast()) {
                $listing->update(['status' => 'expired']);
                throw new RuntimeException('The collection deadline for this listing has already passed.');
            }

            if ($quantity < 1 || $listing->available_quantity < $quantity) {
                throw new RuntimeException("Insufficient stock. Only {$listing->available_quantity} unit(s) remaining.");
            }

            $now = now();
            $threeHoursLater = $now->copy()->addHours(3);
            $expiresAt = $threeHoursLater->lt($listing->collection_deadline)
                ? $threeHoursLater
                : $listing->collection_deadline->copy();

            $unitPrice = (float) $listing->discounted_price;
            $originalUnitPrice = (float) $listing->original_price;
            $totalDiscounted = round($unitPrice * $quantity, 2);
            $originalTotal = round($originalUnitPrice * $quantity, 2);

            // Deduct stock immediately within the locked transaction
            $newQuantity = $listing->available_quantity - $quantity;
            $listing->update([
                'available_quantity' => $newQuantity,
                'status' => $newQuantity === 0 ? 'sold_out' : 'active',
            ]);

            $reservation = Reservation::create([
                'reservation_reference' => $this->generateReference(),
                'buyer_id' => $buyer->id,
                'listing_id' => $listing->id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'original_unit_price' => $originalUnitPrice,
                'total_discounted_price' => $totalDiscounted,
                'original_total_price' => $originalTotal,
                'reserved_at' => $now,
                'expires_at' => $expiresAt,
                'status' => 'pending',
            ]);

            Acknowledgement::create([
                'reservation_id' => $reservation->id,
                'buyer_id' => $buyer->id,
                'acknowledged_product_info' => (bool) ($acknowledgements['acknowledged_product_info'] ?? false),
                'acknowledged_expiry' => (bool) ($acknowledgements['acknowledged_expiry'] ?? false),
                'acknowledged_allergens' => (bool) ($acknowledgements['acknowledged_allergens'] ?? false),
                'acknowledged_storage' => (bool) ($acknowledgements['acknowledged_storage'] ?? false),
                'acknowledged_collection' => (bool) ($acknowledgements['acknowledged_collection'] ?? false),
                'disclaimer_accepted' => (bool) ($acknowledgements['disclaimer_accepted'] ?? false),
                'acknowledged_at' => $now,
            ]);

            StockMovement::create([
                'listing_id' => $listing->id,
                'reservation_id' => $reservation->id,
                'movement_type' => 'deduction',
                'quantity_change' => -$quantity,
                'idempotency_key' => "deduct_res_{$reservation->id}",
                'notes' => "Stock reserved under reference {$reservation->reservation_reference}.",
            ]);

            $this->auditLogService->log(
                action: 'reservation_created',
                targetType: Reservation::class,
                targetId: $reservation->id,
                details: [
                    'reference' => $reservation->reservation_reference,
                    'listing_id' => $listing->id,
                    'quantity' => $quantity,
                    'expires_at' => $expiresAt->toDateTimeString(),
                ],
                userId: $buyer->id
            );

            $this->notificationService->send(
                userId: $buyer->id,
                type: 'reservation_confirmed',
                title: 'Reservation Confirmed',
                message: "Your reservation {$reservation->reservation_reference} for {$quantity}x {$listing->food_name} is confirmed. Collect before {$expiresAt->format('M d, H:i')}.",
                relatedType: Reservation::class,
                relatedId: $reservation->id
            );

            $this->notificationService->send(
                userId: $listing->store->user_id,
                type: 'reservation_confirmed',
                title: 'New Food Reservation',
                message: "Buyer {$buyer->name} reserved {$quantity}x {$listing->food_name} (Ref: {$reservation->reservation_reference}).",
                relatedType: Reservation::class,
                relatedId: $reservation->id
            );

            return $reservation;
        });
    }

    /**
     * Cancel a pending reservation by its buyer and restore stock if eligible.
     */
    public function cancelReservation(User $buyer, int $reservationId): Reservation
    {
        return DB::transaction(function () use ($buyer, $reservationId) {
            /** @var Reservation|null $reservation */
            $reservation = Reservation::where('id', $reservationId)
                ->lockForUpdate()
                ->first();

            if (! $reservation) {
                throw new RuntimeException('Reservation not found.');
            }

            if ($reservation->buyer_id !== $buyer->id) {
                abort(403, 'You are not authorized to cancel this reservation.');
            }

            if ($reservation->status !== 'pending') {
                throw new RuntimeException("Only pending reservations can be cancelled. Current status: {$reservation->status}.");
            }

            $reservation->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);

            $restored = $this->restoreStockIdempotently(
                $reservation,
                "restore_cancel_res_{$reservation->id}",
                "Stock restored after buyer cancellation of {$reservation->reservation_reference}."
            );

            $this->auditLogService->log(
                action: 'reservation_cancelled',
                targetType: Reservation::class,
                targetId: $reservation->id,
                details: [
                    'reference' => $reservation->reservation_reference,
                    'stock_restored' => $restored,
                ],
                userId: $buyer->id
            );

            $reservation->load('listing.store');

            $this->notificationService->send(
                userId: $buyer->id,
                type: 'reservation_cancelled',
                title: 'Reservation Cancelled',
                message: "You cancelled reservation {$reservation->reservation_reference}.",
                relatedType: Reservation::class,
                relatedId: $reservation->id
            );

            $this->notificationService->send(
                userId: $reservation->listing->store->user_id,
                type: 'reservation_cancelled',
                title: 'Buyer Cancelled Reservation',
                message: "Reservation {$reservation->reservation_reference} ({$reservation->quantity}x {$reservation->listing->food_name}) was cancelled by the buyer.",
                relatedType: Reservation::class,
                relatedId: $reservation->id
            );

            return $reservation;
        });
    }

    /**
     * Confirm in-store food collection by the seller using the reservation reference.
     */
    public function confirmCollection(User $seller, string $reference): Reservation
    {
        $cleanRef = strtoupper(trim($reference));

        return DB::transaction(function () use ($seller, $cleanRef) {
            /** @var Reservation|null $reservation */
            $reservation = Reservation::where('reservation_reference', $cleanRef)
                ->lockForUpdate()
                ->first();

            if (! $reservation) {
                throw new RuntimeException("No reservation found with reference {$cleanRef}.");
            }

            $reservation->load(['listing.store', 'buyer']);

            if ($reservation->listing->store->user_id !== $seller->id) {
                abort(403, 'This reservation does not belong to any of your stores.');
            }

            if ($reservation->status === 'collected') {
                throw new RuntimeException('This reservation has already been collected.');
            }

            if ($reservation->status !== 'pending') {
                throw new RuntimeException("Cannot confirm collection. Reservation status is '{$reservation->status}'.");
            }

            if ($reservation->expires_at->isPast() || $reservation->listing->collection_deadline->isPast()) {
                throw new RuntimeException('This reservation has expired and can no longer be collected.');
            }

            $reservation->update([
                'status' => 'collected',
                'collected_at' => now(),
            ]);

            $this->auditLogService->log(
                action: 'reservation_collected',
                targetType: Reservation::class,
                targetId: $reservation->id,
                details: [
                    'reference' => $reservation->reservation_reference,
                    'quantity' => $reservation->quantity,
                    'savings' => $reservation->savings,
                ],
                userId: $seller->id
            );

            $this->notificationService->send(
                userId: $reservation->buyer_id,
                type: 'food_collected',
                title: 'Food Collected Successfully',
                message: "Thank you for collecting {$reservation->quantity}x {$reservation->listing->food_name} (Ref: {$reservation->reservation_reference}). You saved $" . number_format($reservation->savings, 2) . '!',
                relatedType: Reservation::class,
                relatedId: $reservation->id
            );

            return $reservation;
        });
    }

    /**
     * Automatically expire overdue pending reservations and conditionally restore stock.
     * Safe to execute repeatedly (idempotent).
     */
    public function expireOverdueReservations(): int
    {
        $overdueIds = Reservation::where('status', 'pending')
            ->where('expires_at', '<=', now())
            ->pluck('id');

        $expiredCount = 0;

        foreach ($overdueIds as $reservationId) {
            $processed = DB::transaction(function () use ($reservationId) {
                /** @var Reservation|null $reservation */
                $reservation = Reservation::where('id', $reservationId)
                    ->lockForUpdate()
                    ->first();

                // Recheck status inside lock to handle concurrent collection/cancellation safely
                if (! $reservation || $reservation->status !== 'pending' || $reservation->expires_at->isFuture()) {
                    return false;
                }

                $reservation->update(['status' => 'expired']);

                $restored = $this->restoreStockIdempotently(
                    $reservation,
                    "restore_expire_res_{$reservation->id}",
                    "Automatic stock restoration after expiry of {$reservation->reservation_reference}."
                );

                $this->auditLogService->log(
                    action: 'reservation_expired',
                    targetType: Reservation::class,
                    targetId: $reservation->id,
                    details: [
                        'reference' => $reservation->reservation_reference,
                        'stock_restored' => $restored,
                    ],
                    userId: null
                );

                $reservation->load('listing.store');

                $this->notificationService->send(
                    userId: $reservation->buyer_id,
                    type: 'reservation_expired',
                    title: 'Reservation Expired',
                    message: "Your reservation {$reservation->reservation_reference} for {$reservation->listing->food_name} expired because it was not collected in time.",
                    relatedType: Reservation::class,
                    relatedId: $reservation->id
                );

                $this->notificationService->send(
                    userId: $reservation->listing->store->user_id,
                    type: 'reservation_expired',
                    title: 'Reservation Expired',
                    message: "Reservation {$reservation->reservation_reference} ({$reservation->quantity}x {$reservation->listing->food_name}) expired." . ($restored ? ' Stock has been restored.' : ' Stock was not restored because the listing is no longer eligible.'),
                    relatedType: Reservation::class,
                    relatedId: $reservation->id
                );

                return true;
            });

            if ($processed) {
                $expiredCount++;
            }
        }

        // Also mark any active listings whose collection_deadline has passed as expired
        Listing::where('status', 'active')
            ->where('collection_deadline', '<=', now())
            ->update(['status' => 'expired']);

        return $expiredCount;
    }

    /**
     * Conditionally and idempotently restore stock to an eligible listing.
     */
    private function restoreStockIdempotently(Reservation $reservation, string $idempotencyKey, string $notes): bool
    {
        /** @var Listing|null $listing */
        $listing = Listing::with('store')
            ->where('id', $reservation->listing_id)
            ->lockForUpdate()
            ->first();

        if (! $listing) {
            return false;
        }

        // Do NOT restore stock to closed, expired, or inactive-store listings
        $isEligibleForRestoration = in_array($listing->status, ['active', 'sold_out'], true)
            && $listing->collection_deadline->isFuture()
            && $listing->store?->is_active;

        if (! $isEligibleForRestoration) {
            return false;
        }

        // Idempotency check: never restore twice for the same reservation
        if (StockMovement::where('idempotency_key', $idempotencyKey)->exists()) {
            return false;
        }

        try {
            StockMovement::create([
                'listing_id' => $listing->id,
                'reservation_id' => $reservation->id,
                'movement_type' => 'restoration',
                'quantity_change' => $reservation->quantity,
                'idempotency_key' => $idempotencyKey,
                'notes' => $notes,
            ]);
        } catch (QueryException) {
            // Unique constraint on idempotency_key prevented duplicate restoration
            return false;
        }

        $listing->update([
            'available_quantity' => $listing->available_quantity + $reservation->quantity,
            'status' => 'active',
        ]);

        return true;
    }

    private function generateReference(): string
    {
        do {
            $ref = 'RB-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
        } while (Reservation::where('reservation_reference', $ref)->exists());

        return $ref;
    }
}
