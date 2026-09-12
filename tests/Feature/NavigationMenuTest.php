<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class NavigationMenuTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Budi Setiawan',
            'email' => 'budi@dricash.test',
            'password' => bcrypt('password'),
            'currency' => 'IDR',
            'monthly_start_day' => 1,
            'initial_net_worth' => 10000000.00,
        ]);
    }

    public function test_dashboard_renders_beranda_component()
    {
        $response = $this->actingAs($this->user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->has('metrics')
            ->has('categories')
        );
    }

    public function test_transactions_page_renders_transactions_component_on_reload()
    {
        $response = $this->actingAs($this->user)->get('/transactions');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Transactions/Index')
            ->has('transactions')
        );
    }

    public function test_obligations_page_renders_obligations_component_on_reload()
    {
        $response = $this->actingAs($this->user)->get('/obligations');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Obligations/Index')
            ->has('obligations')
        );
    }

    public function test_growth_page_renders_growth_component_on_reload()
    {
        $response = $this->actingAs($this->user)->get('/growth');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Growth/Index')
            ->has('metrics')
            ->has('cashflow')
        );
    }

    public function test_reports_page_renders_reports_component_on_reload()
    {
        $response = $this->actingAs($this->user)->get('/reports');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Reports/Index')
            ->has('metrics')
            ->has('cashflow')
        );
    }
}
