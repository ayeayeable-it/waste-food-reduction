<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Listing;
use App\Models\Reservation;
use App\Models\StockMovement;
use App\Models\Store;
use App\Models\User;
use App\Services\ReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Tests\TestCase;

class ReservationReliabilityTest extends TestCase
{
    use RefreshDatabase;

    private function seedScenario(int $stock = 1, int $deadlineHours = 6): array
    {
        $seller = User::create([
            'name' => 'Seller One',
            'email' => 'seller@example.com',
            'password' => Hash::make('Password123!'),
            'role' => 'seller',
            'is_active' => true,
        ]);

        $buyer1 = User::create([
            'name' => 'Buyer One',
            'email' => 'buyer1@example.com',
            'password' => Hash::make('Password123!'),
            'role' => 'buyer',
            'is_active' => true,
        ]);

        $buyer2 = User::create([
            'name' => 'Buyer Two',
            'email' => 'buyer2@example.com',
            'password' => Hash::make('Password123!'),
            'role' => 'buyer',
            'is_active' => true,
        ]);

        $store = Store::create([
            'user_id' => $seller->id,
            'name' => 'GreenMart',
            'address' => '100 High St',
            'latitude' => 51.5073509,
            'longitude' => -0.1277583,
            'is_active' => true,
        ]);

        $category = Category::create(['name' => 'Bakery', 'slug' => 'bakery']);

        $listing = Listing::create([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'food_name' => 'Sourdough Bag',
            'description' => 'Fresh loaf.',
            'original_price' => 6.00,
            'discounted_price' => 2.50,
            'available_quantity' => $stock,
            'expiry_label' => 'Best Before: Today',
            'storage_instructions' => 'Cool dry place.',
            'allergen_info' => 'Gluten.',
            'collection_deadline' => now()->addHours($deadlineHours),
            'status' => 'active',
        ]);

        return compact('seller', 'buyer1', 'buyer2', 'store', 'category', 'listing');
    }

    private function validAcknowledgements(): array
    {
        return [
            'acknowledged_product_info' => true,
            'acknowledged_expiry' => true,
            'acknowledged_allergens' => true,
            'acknowledged_storage' => true,
            'acknowledged_collection' => true,
            'disclaimer_accepted' => true,
        ];
    }

    public function test_two_buyers_attempting_to_reserve_last_item_prevents_overselling(): void
    {
        $data = $this->seedScenario(stock: 1);
        $service = app(ReservationService::class);

        $first = $service->createReservation($data['buyer1'], $data['listing']->id, 1, $this->validAcknowledgements());
        $this->assertEquals('pending', $first->status);
        $this->assertEquals(0, $data['listing']->fresh()->available_quantity);
        $this->assertEquals('sold_out', $data['listing']->fresh()->status);

        $this->expectException(RuntimeException::class);
        $service->createReservation($data['buyer2'], $data['listing']->id, 1, $this->validAcknowledgements());
    }

    public function test_insufficient_stock_is_rejected_and_stock_never_goes_negative(): void
    {
        $data = $this->seedScenario(stock: 2);
        $service = app(ReservationService::class);

        try {
            $service->createReservation($data['buyer1'], $data['listing']->id, 5, $this->validAcknowledgements());
            $this->fail('Expected RuntimeException for insufficient stock.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('Insufficient stock', $e->getMessage());
        }

        $this->assertEquals(2, $data['listing']->fresh()->available_quantity);
    }

    public function test_reservation_expiry_uses_earlier_of_three_hours_or_collection_deadline(): void
    {
        // Deadline is 1 hour from now (< 3 hours), so expires_at must match collection_deadline
        $data = $this->seedScenario(stock: 3, deadlineHours: 1);
        $service = app(ReservationService::class);

        $reservation = $service->createReservation($data['buyer1'], $data['listing']->id, 1, $this->validAcknowledgements());

        $this->assertEquals(
            $data['listing']->collection_deadline->format('Y-m-d H:i'),
            $reservation->expires_at->format('Y-m-d H:i')
        );
    }

    public function test_expired_reservation_restores_stock_once_and_is_idempotent(): void
    {
        $data = $this->seedScenario(stock: 2, deadlineHours: 6);
        $service = app(ReservationService::class);

        $reservation = $service->createReservation($data['buyer1'], $data['listing']->id, 2, $this->validAcknowledgements());
        $this->assertEquals(0, $data['listing']->fresh()->available_quantity);

        // Force reservation to be overdue
        $reservation->update(['expires_at' => now()->subMinutes(5)]);

        // Run expiry twice to verify idempotency
        $firstRun = $service->expireOverdueReservations();
        $secondRun = $service->expireOverdueReservations();

        $this->assertEquals(1, $firstRun);
        $this->assertEquals(0, $secondRun);
        $this->assertEquals('expired', $reservation->fresh()->status);
        $this->assertEquals(2, $data['listing']->fresh()->available_quantity);
        $this->assertEquals('active', $data['listing']->fresh()->status);
        $this->assertEquals(1, StockMovement::where('movement_type', 'restoration')->count());
    }

    public function test_conditional_stock_restoration_does_not_restore_to_closed_listing(): void
    {
        $data = $this->seedScenario(stock: 3, deadlineHours: 6);
        $service = app(ReservationService::class);

        $reservation = $service->createReservation($data['buyer1'], $data['listing']->id, 2, $this->validAcknowledgements());
        $this->assertEquals(1, $data['listing']->fresh()->available_quantity);

        // Seller closes the listing
        $data['listing']->update(['status' => 'closed']);

        // Buyer cancels reservation
        $service->cancelReservation($data['buyer1'], $reservation->id);

        // Stock must NOT be restored to a closed listing
        $this->assertEquals('cancelled', $reservation->fresh()->status);
        $this->assertEquals(1, $data['listing']->fresh()->available_quantity);
        $this->assertEquals('closed', $data['listing']->fresh()->status);
        $this->assertEquals(0, StockMovement::where('movement_type', 'restoration')->count());
    }

    public function test_collection_after_expiry_is_rejected(): void
    {
        $data = $this->seedScenario(stock: 2, deadlineHours: 6);
        $service = app(ReservationService::class);

        $reservation = $service->createReservation($data['buyer1'], $data['listing']->id, 1, $this->validAcknowledgements());
        $reservation->update(['expires_at' => now()->subMinute()]);

        $this->expectException(RuntimeException::class);
        $service->confirmCollection($data['seller'], $reservation->reservation_reference);
    }

    public function test_unauthorized_buyer_cannot_view_or_cancel_another_buyers_reservation(): void
    {
        $data = $this->seedScenario(stock: 2, deadlineHours: 6);
        $service = app(ReservationService::class);

        $reservation = $service->createReservation($data['buyer1'], $data['listing']->id, 1, $this->validAcknowledgements());

        $this->actingAs($data['buyer2'])->get(route('buyer.reservations.show', $reservation))->assertForbidden();
        $this->actingAs($data['buyer2'])->patch(route('buyer.reservations.cancel', $reservation))->assertForbidden();
    }

    public function test_historical_prices_remain_unchanged_after_seller_updates_listing_price(): void
    {
        $data = $this->seedScenario(stock: 3, deadlineHours: 6);
        $service = app(ReservationService::class);

        $reservation = $service->createReservation($data['buyer1'], $data['listing']->id, 2, $this->validAcknowledgements());

        // Seller changes listing price afterward
        $data['listing']->update(['discounted_price' => 4.50]);

        $reservation->refresh();
        $this->assertEquals(2.50, (float) $reservation->unit_price);
        $this->assertEquals(5.00, (float) $reservation->total_discounted_price);
        $this->assertEquals(7.00, $reservation->savings);
    }
}
