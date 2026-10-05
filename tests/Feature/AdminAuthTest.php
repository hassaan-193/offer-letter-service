<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function createAdmin(string $email = 'admin@example.com', string $password = 'password123'): Admin
    {
        return Admin::create([
            'name'     => 'Test Admin',
            'email'    => $email,
            'password' => Hash::make($password),
        ]);
    }

    public function test_login_page_is_accessible(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Admin Login');
    }

    public function test_admin_can_login_with_valid_credentials(): void
    {
        $this->createAdmin('admin@example.com', 'secret123');

        $response = $this->post('/login', [
            'email'    => 'admin@example.com',
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated('admin');
    }

    public function test_admin_cannot_login_with_invalid_credentials(): void
    {
        $this->createAdmin('admin@example.com', 'secret123');

        $response = $this->from('/login')->post('/login', [
            'email'    => 'admin@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest('admin');
    }

    public function test_admin_can_logout(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin, 'admin')->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest('admin');
    }

    public function test_unauthenticated_user_cannot_access_admin_dashboard(): void
    {
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_admin_can_access_dashboard(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin, 'admin')->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Dashboard');
        $response->assertSee($admin->name);
    }
}
