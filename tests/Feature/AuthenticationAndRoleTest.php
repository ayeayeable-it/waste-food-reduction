<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationAndRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_can_register_and_is_redirected_to_buyer_dashboard(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Test Buyer',
            'email' => 'newbuyer@example.com',
            'phone' => '+44 20 7946 0999',
            'role' => 'buyer',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ]);

        $response->assertRedirect(route('buyer.dashboard'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'newbuyer@example.com',
            'role' => 'buyer',
            'is_active' => true,
        ]);
    }

    public function test_admin_self_registration_is_blocked(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Fake Admin',
            'email' => 'fakeadmin@example.com',
            'role' => 'admin',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertGuest();
    }

    public function test_user_can_login_and_logout(): void
    {
        $user = User::create([
            'name' => 'Alice Buyer',
            'email' => 'alice@example.com',
            'password' => Hash::make('Password123!'),
            'role' => 'buyer',
            'is_active' => true,
        ]);

        $loginResponse = $this->post(route('login.attempt'), [
            'email' => 'alice@example.com',
            'password' => 'Password123!',
        ]);

        $loginResponse->assertRedirect(route('buyer.dashboard'));
        $this->assertAuthenticatedAs($user);

        $logoutResponse = $this->post(route('logout'));
        $logoutResponse->assertRedirect(route('home'));
        $this->assertGuest();
    }

    public function test_deactivated_user_cannot_login(): void
    {
        User::create([
            'name' => 'Blocked User',
            'email' => 'blocked@example.com',
            'password' => Hash::make('Password123!'),
            'role' => 'buyer',
            'is_active' => false,
        ]);

        $response = $this->post(route('login.attempt'), [
            'email' => 'blocked@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertSessionHas('error');
        $this->assertGuest();
    }

    public function test_role_based_access_returns_403_for_unauthorized_roles(): void
    {
        $buyer = User::create([
            'name' => 'Buyer One',
            'email' => 'b1@example.com',
            'password' => Hash::make('Password123!'),
            'role' => 'buyer',
            'is_active' => true,
        ]);

        $this->actingAs($buyer)->get(route('seller.dashboard'))->assertForbidden();
        $this->actingAs($buyer)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($buyer)->get(route('buyer.dashboard'))->assertOk();
    }
}
