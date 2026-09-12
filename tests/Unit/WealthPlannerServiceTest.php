<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\GrowthTarget;
use App\Models\MonthlyObligation;
use App\Models\MonthlyObligationPayment;
use App\Models\Transaction;
use App\Models\User;
use App\Services\WealthPlannerService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WealthPlannerServiceTest extends TestCase
{
    use RefreshDatabase;

    protected WealthPlannerService $service;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new WealthPlannerService();

        $this->user = User::create([
            'name' => 'Tester User',
            'email' => 'tester@dricash.app',
            'password' => bcrypt('secret'),
            'currency' => 'IDR',
            'monthly_start_day' => 1,
            'initial_net_worth' => 20000000.00, // Rp 20.000.000
        ]);
    }

    public function test_dynamic_daily_budget_and_ring_fencing_calculation()
    {
        Carbon::setTestNow(Carbon::create(2026, 9, 10, 12, 0, 0));

        $incomeCat = Category::create(['name' => 'Gaji', 'type' => 'income']);
        $obCat = Category::create(['name' => 'Kos', 'type' => 'obligation']);
        $expCat = Category::create(['name' => 'Makan', 'type' => 'expense']);

        // Income: Rp 10.000.000
        Transaction::create([
            'user_id' => $this->user->id,
            'category_id' => $incomeCat->id,
            'type' => 'income',
            'amount' => 10000000.00,
            'transaction_date' => '2026-09-01',
            'description' => 'Gaji Bulanan',
        ]);

        // Obligation: Rp 3.000.000 (Ring-fenced)
        $obligation = MonthlyObligation::create([
            'user_id' => $this->user->id,
            'category_id' => $obCat->id,
            'name' => 'Sewa Kos',
            'amount' => 3000000.00,
            'due_day' => 5,
            'is_active' => true,
        ]);

        // Growth Target: Rp 1.000.000 savings target (5% of 20jt)
        GrowthTarget::create([
            'user_id' => $this->user->id,
            'period_month' => 9,
            'period_year' => 2026,
            'target_growth_percentage' => 5.00,
            'target_savings_amount' => 1000000.00,
            'starting_net_worth' => 20000000.00,
        ]);

        // Past Daily Expenses (Sep 1 to Sep 9): Rp 900.000
        Transaction::create([
            'user_id' => $this->user->id,
            'category_id' => $expCat->id,
            'type' => 'expense',
            'amount' => 900000.00,
            'transaction_date' => '2026-09-05',
            'description' => 'Makan hari sebelumnya',
        ]);

        // Spent Today (Sep 10): Rp 100.000
        Transaction::create([
            'user_id' => $this->user->id,
            'category_id' => $expCat->id,
            'type' => 'expense',
            'amount' => 100000.00,
            'transaction_date' => '2026-09-10',
            'description' => 'Makan Siang Hari Ini',
        ]);

        $metrics = $this->service->getDashboardMetrics($this->user, 9, 2026);

        // In September (30 days total), Today is day 10 -> remaining days = 30 - 10 + 1 = 21 days
        // Total Income: 10.000.000
        // Total Obligations: 3.000.000
        // Target Savings: 1.000.000
        // Remaining pool for Sep 10-30 = 10.000.000 - 3.000.000 - 1.000.000 - 900.000 = 5.100.000
        // Today's Daily Budget = 5.100.000 / 21 = 242.857,14
        $expectedDailyBudget = round(5100000 / 21, 2);

        $this->assertEquals(10000000.00, $metrics['cashflow']['total_income']);
        $this->assertEquals(3000000.00, $metrics['obligations']['total_amount']);
        $this->assertEquals($expectedDailyBudget, $metrics['daily_budget']['today_budget']);
        $this->assertEquals(100000.00, $metrics['daily_budget']['spent_today']);
        $this->assertEquals(round($expectedDailyBudget - 100000, 2), $metrics['daily_budget']['remaining_today']);
        $this->assertEquals('safe', $metrics['daily_budget']['status']);
    }

    public function test_growth_target_intervention_detects_breach()
    {
        Carbon::setTestNow(Carbon::create(2026, 9, 10, 12, 0, 0));

        $incomeCat = Category::create(['name' => 'Gaji', 'type' => 'income']);

        Transaction::create([
            'user_id' => $this->user->id,
            'category_id' => $incomeCat->id,
            'type' => 'income',
            'amount' => 5000000.00,
            'transaction_date' => '2026-09-01',
            'description' => 'Gaji',
        ]);

        // Target: 5% growth on 20jt = +Rp 1.000.000 month-end target net worth >= 21.000.000
        GrowthTarget::create([
            'user_id' => $this->user->id,
            'period_month' => 9,
            'period_year' => 2026,
            'target_growth_percentage' => 5.00,
            'target_savings_amount' => 1000000.00,
            'starting_net_worth' => 20000000.00,
        ]);

        // 1. Safe expense check (Rp 500.000)
        $safeCheck = $this->service->checkGrowthIntervention($this->user, 500000.00, '2026-09-10');
        $this->assertFalse($safeCheck['breaches_target']);

        // 2. Heavy expense that consumes savings and breaches target (Rp 4.500.000)
        $breachCheck = $this->service->checkGrowthIntervention($this->user, 4500000.00, '2026-09-10');
        $this->assertTrue($breachCheck['breaches_target']);
        $this->assertNotNull($breachCheck['warning_message']);
        $this->assertLessThan(5.00, $breachCheck['new_projected_percentage']);
    }
}
