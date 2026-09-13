<?php

namespace App\Http\Controllers;

use App\Models\GrowthTarget;
use App\Services\WealthPlannerService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GrowthTargetController extends Controller
{
    public function __construct(
        protected WealthPlannerService $wealthService
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $now = Carbon::now();
        $month = $request->filled('month') ? (int) $request->input('month') : (int) $now->format('m');
        $year = $request->filled('year') ? (int) $request->input('year') : (int) $now->format('Y');

        $metrics = $this->wealthService->getDashboardMetrics($user, $month, $year);

        $target = GrowthTarget::firstOrNew([
            'user_id' => $user->id,
            'period_month' => $month,
            'period_year' => $year,
        ], [
            'target_growth_percentage' => 5.00,
            'target_savings_amount' => 0.00,
            'starting_net_worth' => $metrics['growth']['starting_net_worth'] ?? 0.00,
        ]);

        return Inertia::render('Growth/Index', [
            'metrics' => $metrics['growth'],
            'cashflow' => $metrics['cashflow'],
            'charts' => $metrics['charts'],
            'period' => $metrics['period'],
            'currentTarget' => [
                'target_growth_percentage' => (float) $target->target_growth_percentage,
                'target_savings_amount' => (float) $target->target_savings_amount,
                'notes' => $target->notes,
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'period_month' => ['required', 'integer', 'min:1', 'max:12'],
            'period_year' => ['required', 'integer', 'min:2020', 'max:2050'],
            'target_growth_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'target_savings_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $user = $request->user();

        // Atur target TIDAK mempengaruhi jumlah uang / saldo user.
        // Jumlah uang hanya terupdate dari transaksi pengeluaran dan pemasukan.
        $metrics = $this->wealthService->getDashboardMetrics($user, $validated['period_month'], $validated['period_year'], false);
        $computedStartingNetWorth = (float) ($metrics['growth']['starting_net_worth'] ?? 0.00);

        GrowthTarget::updateOrCreate(
            [
                'user_id' => $user->id,
                'period_month' => $validated['period_month'],
                'period_year' => $validated['period_year'],
            ],
            [
                'target_growth_percentage' => $validated['target_growth_percentage'],
                'target_savings_amount' => $validated['target_savings_amount'] ?? 0.00,
                'starting_net_worth' => $computedStartingNetWorth,
                'notes' => $validated['notes'] ?? null,
            ]
        );

        WealthPlannerService::clearUserCache($user->id);

        return back()->with('success', "Target pertumbuhan ({$validated['target_growth_percentage']}%) berhasil diperbarui!");
    }
}
