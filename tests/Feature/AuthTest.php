<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_as_customer(): void
    {
        $response = $this->post('/register', [
            'name' => 'Budi & Dewi',
            'email' => 'budi.dewi@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'budi.dewi@example.com',
            'role' => 'customer',
        ]);
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'testuser@undangan.id',
            'password' => bcrypt('password123'),
            'role' => 'customer',
        ]);

        $response = $this->post('/login', [
            'email' => 'testuser@undangan.id',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'email' => 'testuser@undangan.id',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'testuser@undangan.id',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_admin_must_use_the_password_saved_for_the_account(): void
    {
        User::factory()->create([
            'email' => 'admin@undangan.id',
            'password' => bcrypt('a-real-admin-password'),
            'role' => 'admin',
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@undangan.id',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
