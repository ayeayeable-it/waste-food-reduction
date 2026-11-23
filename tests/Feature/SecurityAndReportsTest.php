<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Listing;
use App\Models\Store;
use App\Models\User;
use App\Services\ReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SecurityAndReportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_sql_injection_payload_in_discovery_search_is_safely_parameterized(): void
    {
        $buyer = User::create([
            'name' => 'Buyer Safe',
            'email' => 'safe@example.com',
            'password' => Hash::make('Password123!'),
            'role' => 'buyer',
            'is_active' => true,
        ]);

        $response = $this->actingAs($buyer)->get(route('buyer.discover', [
            'search' => "' OR '1'='1'; DROP TABLE users; --",
        ]));

        $response->assertOk();
        $this->assertDatabaseHas('users', ['email' => 'safe@example.com']);
    }

    public function test_invalid_non_image_upload_is_rejected(): void
    {
        $seller = User::create([
            'name' => 'Seller Upload',
            'email' => 'upload@example.com',
            'password' => Hash::make('Password123!'),
            'role' => 'seller',
            'is_active' => true,
        ]);
        $store = Store::create([
            'user_id' => $seller->id,
            'name' => 'Test Store',
            'address' => '1 Test St',
            'latitude' => 51.50,
            'longitude' => -0.12,
            'is_active' => true,
        ]);
        $category = Category::create(['name' => 'Pantry', 'slug' => 'pantry']);

        $response = $this->actingAs($seller)->post(route('seller.listings.store'), [
            'store_id' => $store->id,
            'category_id' => $category->id,
            'food_name' => 'Malicious File Test',
            'description' => 'Testing file validation.',
            'image' => UploadedFile::fake()->create('exploit.php', 50, 'application/x-httpd-php'),
            'original_price' => 5.00,
            'discounted_price' => 2.00,
            'available_quantity' => 2,
            'expiry_label' => 'Today',
            'storage_instructions' => 'Dry.',
            'allergen_info' => 'None.',
            'collection_deadline' => now()->addHours(4)->format('Y-m-d H:i:s'),
        ]);

        $response->assertSessionHasErrors('image');
    }

    public function test_xss_script_tags_in_food_name_are_escaped_in_blade_output(): void
    {
        $seller = User::create([
            'name' => 'Seller XSS',
            'email' => 'xss@example.com',
            'password' => Hash::make('Password123!'),
            'role' => 'seller',
            'is_active' => true,
        ]);
        $buyer = User::create([
            'name' => 'Buyer XSS',
            'email' => 'buyerxss@example.com',
            'password' => Hash::make('Password123!'),
            'role' => 'buyer',
            'is_active' => true,
        ]);
        $store = Store::create([
            'user_id' => $seller->id,
            'name' => 'Safe Store',
            'address' => '2 Safe St',
            'latitude' => 51.50,
            'longitude' => -0.12,
            'is_active' => true,
        ]);
        $category = Category::create(['name' => 'Drinks', 'slug' => 'drinks']);

        $listing = Listing::create([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'food_name' => '<script>alert("xss")</script>Juice',
            'description' => 'Fresh juice.',
            'original_price' => 4.00,
            'discounted_price' => 1.50,
            'available_quantity' => 3,
            'expiry_label' => 'Today',
            'storage_instructions' => 'Chill.',
            'allergen_info' => 'None.',
            'collection_deadline' => now()->addHours(4),
            'status' => 'active',
        ]);

        $response = $this->actingAs($buyer)->get(route('buyer.listings.show', $listing));
        $response->assertOk();
        $response->assertDontSee('<script>alert("xss")</script>', false);
        $response->assertSee('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', false);
    }

    public function test_admin_reports_calculate_consumer_savings_from_collected_orders_only(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('Password123!'),
            'role' => 'admin',
            'is_active' => true,
        ]);
        $seller = User::create([
            'name' => 'Seller',
            'email' => 'srep@example.com',
            'password' => Hash::make('Password123!'),
            'role' => 'seller',
            'is_active' => true,
        ]);
        $buyer = User::create([
            'name' => 'Buyer',
            'email' => 'brep@example.com',
            'password' => Hash::make('Password123!'),
            'role' => 'buyer',
            'is_active' => true,
        ]);
        $store = Store::create([
            'user_id' => $seller->id,
            'name' => 'Report Store',
            'address' => '5 Market St',
            'latitude' => 51.50,
            'longitude' => -0.12,
            'is_active' => true,
        ]);
        $category = Category::create(['name' => 'Produce', 'slug' => 'produce']);
        $listing = Listing::create([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'food_name' => 'Salad Pack',
            'description' => 'Fresh greens.',
            'original_price' => 5.00,
            'discounted_price' => 2.00,
            'available_quantity' => 5,
            'expiry_label' => 'Today',
            'storage_instructions' => 'Refrigerate.',
            'allergen_info' => 'None.',
            'collection_deadline' => now()->addHours(5),
            'status' => 'active',
        ]);

        $acks = [
            'acknowledged_product_info' => true,
            'acknowledged_expiry' => true,
            'acknowledged_allergens' => true,
            'acknowledged_storage' => true,
            'acknowledged_collection' => true,
            'disclaimer_accepted' => true,
        ];

        $service = app(ReservationService::class);
        $collectedRes = $service->createReservation($buyer, $listing->id, 2, $acks);
        $service->confirmCollection($seller, $collectedRes->reservation_reference);

        // Pending reservation should NOT count toward primary savings
        $service->createReservation($buyer, $listing->id, 1, $acks);

        $response = $this->actingAs($admin)->get(route('admin.reports.index'));
        $response->assertOk();
        $response->assertSee('$6.00'); // 2 units * ($5.00 - $2.00) = $6.00
    }
}
