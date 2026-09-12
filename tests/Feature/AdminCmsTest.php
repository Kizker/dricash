<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCmsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@dricash.app',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
            'currency' => 'IDR',
        ]);

        $this->regularUser = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@dricash.app',
            'password' => bcrypt('password'),
            'role' => 'user',
            'is_active' => true,
            'currency' => 'IDR',
        ]);
    }

    public function test_guest_is_redirected_to_login_when_accessing_admin()
    {
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_regular_user_cannot_access_admin_dashboard()
    {
        $response = $this->actingAs($this->regularUser)->get('/admin/dashboard');
        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('error');
    }

    public function test_admin_can_access_dashboard_and_pages()
    {
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        $response->assertStatus(200);

        $usersResponse = $this->actingAs($this->admin)->get('/admin/users');
        $usersResponse->assertStatus(200);

        $categoriesResponse = $this->actingAs($this->admin)->get('/admin/categories');
        $categoriesResponse->assertStatus(200);
    }

    public function test_admin_can_toggle_user_status_and_suspended_user_cannot_login()
    {
        // Toggle status to inactive
        $response = $this->actingAs($this->admin)->post("/admin/users/{$this->regularUser->id}/toggle-status");
        $response->assertSessionHas('success');

        $this->regularUser->refresh();
        $this->assertFalse($this->regularUser->is_active);

        // Attempt login as suspended user
        auth()->logout();
        $loginResponse = $this->post('/login', [
            'email' => 'budi@dricash.app',
            'password' => 'password',
        ]);
        $loginResponse->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_admin_can_update_user_role()
    {
        $response = $this->actingAs($this->admin)->put("/admin/users/{$this->regularUser->id}/role", [
            'role' => 'admin',
        ]);
        $response->assertSessionHas('success');

        $this->regularUser->refresh();
        $this->assertEquals('admin', $this->regularUser->role);
    }

    public function test_admin_can_reset_user_password()
    {
        $response = $this->actingAs($this->admin)->post("/admin/users/{$this->regularUser->id}/reset-password", [
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);
        $response->assertSessionHas('success');

        auth()->logout();
        $loginResponse = $this->post('/login', [
            'email' => 'budi@dricash.app',
            'password' => 'newpassword123',
        ]);
        $loginResponse->assertRedirect('/dashboard');
    }

    public function test_admin_can_create_update_and_delete_default_category()
    {
        // Create
        $response = $this->actingAs($this->admin)->post('/admin/categories', [
            'name' => 'Donasi & Sedekah',
            'type' => 'expense',
            'icon' => 'Heart',
            'color' => '#10B981',
        ]);
        $response->assertSessionHas('success');

        $category = Category::where('name', 'Donasi & Sedekah')->first();
        $this->assertNotNull($category);
        $this->assertTrue($category->is_default);

        // Update
        $updateResponse = $this->actingAs($this->admin)->put("/admin/categories/{$category->id}", [
            'name' => 'Zakat & Sedekah',
            'type' => 'expense',
            'icon' => 'Heart',
            'color' => '#06B6D4',
        ]);
        $updateResponse->assertSessionHas('success');
        $category->refresh();
        $this->assertEquals('Zakat & Sedekah', $category->name);

        // Delete
        $deleteResponse = $this->actingAs($this->admin)->delete("/admin/categories/{$category->id}");
        $deleteResponse->assertSessionHas('success');
        $this->assertSoftDeleted('categories', ['id' => $category->id]);
    }
}
