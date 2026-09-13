<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\GrowthTarget;
use App\Models\MonthlyObligation;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Category $incomeCat;
    protected Category $expenseCat;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Demo User',
            'email' => 'demo@dricash.app',
            'password' => bcrypt('password'),
            'currency' => 'IDR',
            'monthly_start_day' => 1,
            'initial_net_worth' => 15000000.00,
        ]);

        $this->incomeCat = Category::create(['name' => 'Gaji Pokok', 'type' => 'income', 'color' => '#10B981']);
        $this->expenseCat = Category::create(['name' => 'Makan & Minum', 'type' => 'expense', 'color' => '#F43F5E']);
    }

    public function test_user_can_login_and_view_dashboard()
    {
        $response = $this->post('/login', [
            'email' => 'demo@dricash.app',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->user);

        $dashboardResponse = $this->actingAs($this->user)->get('/dashboard');
        $dashboardResponse->assertStatus(200);
    }

    public function test_user_can_store_quick_income()
    {
        $response = $this->actingAs($this->user)->post('/quick-entry', [
            'type' => 'income',
            'amount' => 5000000.00,
            'category_id' => $this->incomeCat->id,
            'transaction_date' => '2026-09-10',
            'description' => 'Gaji Freelance',
            'payment_method' => 'Bank',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'type' => 'income',
            'amount' => 5000000.00,
            'description' => 'Gaji Freelance',
        ]);
    }

    public function test_quick_entry_triggers_intervention_when_target_breached()
    {
        Carbon::setTestNow(Carbon::create(2026, 9, 10, 12, 0, 0));

        // Create Income of 5jt
        Transaction::create([
            'user_id' => $this->user->id,
            'category_id' => $this->incomeCat->id,
            'type' => 'income',
            'amount' => 5000000.00,
            'transaction_date' => '2026-09-01',
            'description' => 'Gaji Awal Bulan',
        ]);

        // Target: 5% of 15jt = +750.000 (Allows max 4.25jt spending in month)
        GrowthTarget::create([
            'user_id' => $this->user->id,
            'period_month' => 9,
            'period_year' => 2026,
            'target_growth_percentage' => 5.00,
            'target_savings_amount' => 750000.00,
            'starting_net_worth' => 15000000.00,
        ]);

        // Attempting to spend 4.8jt (which leaves only 200k, below 750k goal)
        $response = $this->actingAs($this->user)->post('/quick-entry', [
            'type' => 'expense',
            'amount' => 4800000.00,
            'category_id' => $this->expenseCat->id,
            'transaction_date' => '2026-09-10',
            'description' => 'Belanja Komputer Mewah',
            'payment_method' => 'Bank',
            'force_override' => false,
        ]);

        $response->assertSessionHas('intervention');
        $this->assertDatabaseMissing('transactions', [
            'description' => 'Belanja Komputer Mewah',
        ]);

        // Now submit with force_override = true
        $overrideResponse = $this->actingAs($this->user)->post('/quick-entry', [
            'type' => 'expense',
            'amount' => 4800000.00,
            'category_id' => $this->expenseCat->id,
            'transaction_date' => '2026-09-10',
            'description' => 'Belanja Komputer Mewah',
            'payment_method' => 'Bank',
            'force_override' => true,
        ]);

        $overrideResponse->assertSessionMissing('intervention');
        $this->assertDatabaseHas('transactions', [
            'description' => 'Belanja Komputer Mewah',
            'is_growth_overridden' => true,
        ]);
    }

    public function test_user_can_toggle_monthly_obligation_payment()
    {
        $obligation = MonthlyObligation::create([
            'user_id' => $this->user->id,
            'category_id' => $this->expenseCat->id,
            'name' => 'Tagihan Internet',
            'amount' => 350000.00,
            'due_day' => 15,
            'is_active' => true,
        ]);

        // Toggle to paid
        $response = $this->actingAs($this->user)->post("/obligations/{$obligation->id}/toggle");
        $response->assertRedirect();

        $this->assertDatabaseHas('monthly_obligation_payments', [
            'monthly_obligation_id' => $obligation->id,
            'is_paid' => true,
            'paid_amount' => 350000.00,
        ]);

        // Corresponding expense transaction created
        $this->assertDatabaseHas('transactions', [
            'monthly_obligation_id' => $obligation->id,
            'type' => 'expense',
            'amount' => 350000.00,
        ]);

        // Toggle back to unpaid
        $responseUnpaid = $this->actingAs($this->user)->post("/obligations/{$obligation->id}/toggle");
        $responseUnpaid->assertRedirect();

        $this->assertDatabaseHas('monthly_obligation_payments', [
            'monthly_obligation_id' => $obligation->id,
            'is_paid' => false,
        ]);
    }

    public function test_installment_obligation_tracks_remaining_and_updates_on_toggle()
    {
        Carbon::setTestNow(Carbon::create(2026, 9, 10, 12, 0, 0));

        $cicilan = MonthlyObligation::create([
            'user_id' => $this->user->id,
            'category_id' => $this->expenseCat->id,
            'name' => 'Cicilan Laptop',
            'amount' => 1000000.00,
            'due_day' => 10,
            'total_installments' => 12,
            'paid_installments' => 3,
            'is_active' => true,
        ]);

        // Access index page
        $response = $this->actingAs($this->user)->get('/obligations');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Obligations/Index')
            ->has('obligations', 1)
            ->where('obligations.0.total_installments', 12)
            ->where('obligations.0.paid_installments', 3)
            ->where('obligations.0.remaining_installments', 9)
            ->where('obligations.0.remaining_amount', 9000000)
            ->has('wealth')
        );

        // Toggle payment to paid -> paid_installments increments to 4
        $toggleRes = $this->actingAs($this->user)->post("/obligations/{$cicilan->id}/toggle");
        $toggleRes->assertRedirect();

        $cicilan->refresh();
        $this->assertEquals(4, $cicilan->paid_installments);

        // Toggle back to unpaid -> paid_installments decrements back to 3
        $toggleBackRes = $this->actingAs($this->user)->post("/obligations/{$cicilan->id}/toggle");
        $toggleBackRes->assertRedirect();

        $cicilan->refresh();
        $this->assertEquals(3, $cicilan->paid_installments);
    }

    public function test_user_can_update_growth_target()
    {
        $originalNetWorth = $this->user->initial_net_worth;
        $service = app(\App\Services\WealthPlannerService::class);
        $beforeMetrics = $service->getDashboardMetrics($this->user, 9, 2026, false);

        // Atur target tanpa mengirimkan starting_net_worth
        $response = $this->actingAs($this->user)->post('/growth', [
            'period_month' => 9,
            'period_year' => 2026,
            'target_growth_percentage' => 7.50,
            'target_savings_amount' => 2000000.00,
            'notes' => 'Target tabungan kuartal 3',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('growth_targets', [
            'user_id' => $this->user->id,
            'period_month' => 9,
            'period_year' => 2026,
            'target_growth_percentage' => 7.50,
            'target_savings_amount' => 2000000.00,
        ]);

        // Pastikan initial_net_worth user TIDAK berubah sama sekali oleh atur target
        $this->assertEquals($originalNetWorth, $this->user->fresh()->initial_net_worth);

        // Pastikan jumlah uang/current_net_worth tetap sama persis setelah atur target
        $afterTargetMetrics = $service->getDashboardMetrics($this->user, 9, 2026, false);
        $this->assertEquals($beforeMetrics['growth']['current_net_worth'], $afterTargetMetrics['growth']['current_net_worth']);

        // Pastikan jumlah uang HANYA terupdate saat ada input pemasukan / pengeluaran
        Transaction::create([
            'user_id' => $this->user->id,
            'category_id' => $this->incomeCat->id,
            'type' => 'income',
            'amount' => 1500000.00,
            'transaction_date' => '2026-09-12',
            'description' => 'Freelance Tambahan',
        ]);

        $afterIncomeMetrics = $service->getDashboardMetrics($this->user, 9, 2026, false);
        $this->assertEquals(
            $beforeMetrics['growth']['current_net_worth'] + 1500000.00,
            $afterIncomeMetrics['growth']['current_net_worth']
        );
    }

    public function test_transaction_ledger_filters_accurately()
    {
        Transaction::create([
            'user_id' => $this->user->id,
            'category_id' => $this->incomeCat->id,
            'type' => 'income',
            'amount' => 10000000.00,
            'transaction_date' => '2026-09-01',
            'description' => 'Gaji Pokok Transfer BCA',
        ]);

        Transaction::create([
            'user_id' => $this->user->id,
            'category_id' => $this->expenseCat->id,
            'type' => 'expense',
            'amount' => 75000.00,
            'transaction_date' => '2026-09-02',
            'description' => 'Kopi Latte Cafe',
        ]);

        // Filter by search=Latte
        $searchResponse = $this->actingAs($this->user)->get('/transactions?search=Latte');
        $searchResponse->assertStatus(200);
    }

    public function test_user_can_upload_and_remove_profile_avatar()
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $file = \Illuminate\Http\UploadedFile::fake()->image('my-photo.jpg', 200, 200);

        $response = $this->actingAs($this->user)->post('/profile', [
            'name' => 'Andricha Updated',
            'currency' => 'IDR',
            'monthly_start_day' => 5,
            'initial_net_worth' => 25000000.00,
            'avatar' => $file,
        ]);

        $response->assertRedirect();
        $this->user->refresh();
        $this->assertNotNull($this->user->avatar_url);
        $this->assertStringStartsWith('/storage/avatars/', $this->user->avatar_url);

        // Test remove avatar
        $removeResponse = $this->actingAs($this->user)->post('/profile', [
            'name' => 'Andricha Updated',
            'currency' => 'IDR',
            'monthly_start_day' => 5,
            'initial_net_worth' => 25000000.00,
            'remove_avatar' => true,
        ]);

        $removeResponse->assertRedirect();
        $this->user->refresh();
        $this->assertNull($this->user->avatar_url);
    }

    public function test_registration_savings_input_is_recorded_as_income_transaction()
    {
        $regResponse = $this->post('/register', [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'initial_net_worth' => 5000000.00, // Rp 5.000.000
        ]);

        $regResponse->assertRedirect(route('dashboard'));

        $newUser = \App\Models\User::where('email', 'budi@example.com')->first();
        $this->assertNotNull($newUser);

        // Assert that an income transaction of 5,000,000 was created for this user
        $incomeTx = \App\Models\Transaction::where('user_id', $newUser->id)
            ->where('type', 'income')
            ->first();

        $this->assertNotNull($incomeTx);
        $this->assertEquals(5000000.00, (float) $incomeTx->amount);
        $this->assertEquals('Saldo Tabungan Awal', $incomeTx->description);
    }
}
