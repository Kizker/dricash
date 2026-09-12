<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Services\WealthPlannerService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyBudgetTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Category $incomeCat;
    protected Category $expenseCat;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Budi Finansial',
            'email' => 'budi@dricash.app',
            'password' => bcrypt('password'),
            'currency' => 'IDR',
            'monthly_start_day' => 1,
            'initial_net_worth' => 10000000.00,
            'daily_budget_mode' => 'auto',
            'manual_daily_budget' => null,
        ]);

        $this->incomeCat = Category::create(['name' => 'Gaji Pokok', 'type' => 'income', 'color' => '#10B981']);
        $this->expenseCat = Category::create(['name' => 'Jajan & Kopi', 'type' => 'expense', 'color' => '#F43F5E']);
    }

    public function test_default_mode_is_auto_and_calculates_dynamically()
    {
        $todayStr = Carbon::now()->format('Y-m-d');

        // Income 6.000.000
        Transaction::create([
            'user_id' => $this->user->id,
            'category_id' => $this->incomeCat->id,
            'type' => 'income',
            'amount' => 6000000,
            'transaction_date' => $todayStr,
            'payment_method' => 'Bank',
            'description' => 'Gaji Pokok',
        ]);

        $service = app(WealthPlannerService::class);
        $metrics = $service->getDashboardMetrics($this->user);

        $this->assertEquals('auto', $metrics['daily_budget']['mode']);
        $this->assertGreaterThan(0, $metrics['daily_budget']['today_budget']);
        $this->assertEquals(0, $metrics['daily_budget']['manual_budget']);
    }

    public function test_user_can_set_manual_daily_budget_settings()
    {
        $response = $this->actingAs($this->user)->post('/daily-budget/settings', [
            'daily_budget_mode' => 'manual',
            'manual_daily_budget' => 75000,
        ]);

        $response->assertSessionHas('success');
        $this->user->refresh();

        $this->assertEquals('manual', $this->user->daily_budget_mode);
        $this->assertEquals(75000, (float) $this->user->manual_daily_budget);
    }

    public function test_manual_mode_accurately_influences_daily_budget_and_progress()
    {
        $this->user->update([
            'daily_budget_mode' => 'manual',
            'manual_daily_budget' => 50000.00,
        ]);

        $todayStr = Carbon::now()->format('Y-m-d');

        // Expense today: 25.000
        Transaction::create([
            'user_id' => $this->user->id,
            'category_id' => $this->expenseCat->id,
            'type' => 'expense',
            'amount' => 25000,
            'transaction_date' => $todayStr,
            'payment_method' => 'Cash / Tunai',
            'description' => 'Makan Siang',
        ]);

        $service = app(WealthPlannerService::class);
        $metrics = $service->getDashboardMetrics($this->user);

        $dailyBudget = $metrics['daily_budget'];
        $this->assertEquals('manual', $dailyBudget['mode']);
        $this->assertEquals(50000.00, $dailyBudget['today_budget']);
        $this->assertEquals(25000.00, $dailyBudget['spent_today']);
        $this->assertEquals(25000.00, $dailyBudget['remaining_today']);
        $this->assertEquals(50.0, $dailyBudget['progress_percentage']);
        $this->assertEquals('safe', $dailyBudget['status']);
    }

    public function test_manual_mode_detects_overbudget_when_limit_exceeded()
    {
        $this->user->update([
            'daily_budget_mode' => 'manual',
            'manual_daily_budget' => 40000.00,
        ]);

        $todayStr = Carbon::now()->format('Y-m-d');

        // Expense today: 60.000 (exceeds 40.000)
        Transaction::create([
            'user_id' => $this->user->id,
            'category_id' => $this->expenseCat->id,
            'type' => 'expense',
            'amount' => 60000,
            'transaction_date' => $todayStr,
            'payment_method' => 'E-Wallet',
            'description' => 'Ngopi dan Jajan',
        ]);

        $service = app(WealthPlannerService::class);
        $metrics = $service->getDashboardMetrics($this->user);

        $dailyBudget = $metrics['daily_budget'];
        $this->assertEquals('manual', $dailyBudget['mode']);
        $this->assertEquals(40000.00, $dailyBudget['today_budget']);
        $this->assertEquals(60000.00, $dailyBudget['spent_today']);
        $this->assertEquals(-20000.00, $dailyBudget['remaining_today']);
        $this->assertEquals('overbudget', $dailyBudget['status']);
    }

    public function test_user_can_switch_back_to_auto_mode()
    {
        $this->user->update([
            'daily_budget_mode' => 'manual',
            'manual_daily_budget' => 100000.00,
        ]);

        $response = $this->actingAs($this->user)->post('/daily-budget/settings', [
            'daily_budget_mode' => 'auto',
        ]);

        $response->assertSessionHas('success');
        $this->user->refresh();

        $this->assertEquals('auto', $this->user->daily_budget_mode);
        $this->assertNull($this->user->manual_daily_budget);
    }

    public function test_validation_rejects_invalid_mode()
    {
        $response = $this->actingAs($this->user)->post('/daily-budget/settings', [
            'daily_budget_mode' => 'unsupported_mode',
        ]);

        $response->assertSessionHasErrors(['daily_budget_mode']);
    }
}
