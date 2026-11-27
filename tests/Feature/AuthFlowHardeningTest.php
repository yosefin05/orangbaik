<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthFlowHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_is_redirected_to_email_verification(): void
    {
        $email = 'donatur_baru_' . uniqid() . '@example.com';

        $response = $this->post(route('register'), [
            'name' => 'Donatur Baru',
            'email' => $email,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect(route('verification.notice'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => $email,
            'name' => 'Donatur Baru',
        ]);
    }

    public function test_user_cannot_register_with_duplicate_email(): void
    {
        $existing = User::factory()->create([
            'email' => 'duplicate_check@example.com',
        ]);

        $response = $this->post(route('register'), [
            'name' => 'Another User',
            'email' => 'duplicate_check@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_regular_user_login_redirects_to_home(): void
    {
        $user = User::factory()->create([
            'email' => 'user_test_' . uniqid() . '@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_user_login_redirects_to_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin_test_' . uniqid() . '@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $response = $this->post(route('login'), [
            'email' => $admin->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }
}
