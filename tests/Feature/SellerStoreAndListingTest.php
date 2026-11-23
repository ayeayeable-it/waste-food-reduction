<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Listing;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SellerStoreAndListingTest extends TestCase
{
    use RefreshDatabase;

    private function createSeller(string $email): User
    {
        return User::create([
            'name' => 'Seller ' . $email,
            'email' => $email,
            'password' => Hash::make('Password123!'),
            'role' => 'seller',
            'is_active' => true,
        ]);
    }

    public function test_seller_can_register_store_and_create_listing_with_valid_image(): void
    {
        Storage::fake('public');
        $seller = $this->createSeller('s1@example.com');
        $category = Category::create(['name' => 'Bakery', 'slug' => 'bakery']);

        $storeResp = $this->actingAs($seller)->post(route('seller.stores.store'), [
            'name' => 'FreshStop Market',
            'address' => '10 Main Street',
            'latitude' => 51.5073509,
            'longitude' => -0.1277583,
            'collection_instructions' => 'Ask at counter 1.',
            'is_active' => 1,
        ]);

        $storeResp->assertRedirect(route('seller.stores.index'));
        $store = Store::where('name', 'FreshStop Market')->firstOrFail();

        $listingResp = $this->actingAs($seller)->post(route('seller.listings.store'), [
            'store_id' => $store->id,
            'category_id' => $category->id,
            'food_name' => 'Evening Bakery Box',
            'description' => 'Assorted loaves and rolls.',
            'image' => UploadedFile::fake()->image('bread.jpg'),
            'original_price' => 8.00,
            'discounted_price' => 3.50,
            'available_quantity' => 4,
            'expiry_label' => 'Best Before: Today',
            'storage_instructions' => 'Cool dry place.',
            'allergen_info' => 'Contains gluten.',
            'collection_deadline' => now()->addHours(5)->format('Y-m-d H:i:s'),
        ]);

        $listingResp->assertRedirect(route('seller.listings.index'));
        $this->assertDatabaseHas('listings', [
            'food_name' => 'Evening Bakery Box',
            'available_quantity' => 4,
            'status' => 'active',
        ]);
    }

    public function test_listing_validation_rejects_discounted_price_higher_than_original(): void
    {
        $seller = $this->createSeller('s2@example.com');
        $category = Category::create(['name' => 'Dairy', 'slug' => 'dairy']);
        $store = Store::create([
            'user_id' => $seller->id,
            'name' => 'DairyMart',
            'address' => '22 High Rd',
            'latitude' => 51.51,
            'longitude' => -0.13,
            'is_active' => true,
        ]);

        $response = $this->actingAs($seller)->post(route('seller.listings.store'), [
            'store_id' => $store->id,
            'category_id' => $category->id,
            'food_name' => 'Milk Pack',
            'description' => 'Fresh milk.',
            'original_price' => 3.00,
            'discounted_price' => 4.00,
            'available_quantity' => 2,
            'expiry_label' => 'Use By: Tomorrow',
            'storage_instructions' => 'Refrigerate.',
            'allergen_info' => 'Milk.',
            'collection_deadline' => now()->addHours(4)->format('Y-m-d H:i:s'),
        ]);

        $response->assertSessionHasErrors('discounted_price');
    }

    public function test_seller_cannot_edit_another_sellers_store_or_listing(): void
    {
        $sellerA = $this->createSeller('sellera@example.com');
        $sellerB = $this->createSeller('sellerb@example.com');
        $category = Category::create(['name' => 'Snacks', 'slug' => 'snacks']);

        $storeA = Store::create([
            'user_id' => $sellerA->id,
            'name' => 'Store A',
            'address' => '1 Alpha Way',
            'latitude' => 51.50,
            'longitude' => -0.12,
            'is_active' => true,
        ]);

        $listingA = Listing::create([
            'store_id' => $storeA->id,
            'category_id' => $category->id,
            'food_name' => 'Wrap Box',
            'description' => 'Two wraps.',
            'original_price' => 6.00,
            'discounted_price' => 2.50,
            'available_quantity' => 3,
            'expiry_label' => 'Use By: Today',
            'storage_instructions' => 'Keep chilled.',
            'allergen_info' => 'Gluten.',
            'collection_deadline' => now()->addHours(5),
            'status' => 'active',
        ]);

        $this->actingAs($sellerB)->get(route('seller.stores.edit', $storeA))->assertForbidden();
        $this->actingAs($sellerB)->get(route('seller.listings.edit', $listingA))->assertForbidden();
        $this->actingAs($sellerB)->patch(route('seller.listings.close', $listingA))->assertForbidden();
    }
}
