<?php

namespace App\Services;

use App\Models\Category;
use App\Models\GrowthTarget;
use App\Models\MonthlyObligation;
use App\Models\MonthlyObligationPayment;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class WealthPlannerService
{
    /**
     * Get complete financial metrics and state for the given user and period with caching.
     */
    public function getDashboardMetrics(User $user, ?int $month = null, ?int $year = null, bool $useCache = true): array
    {
        $now = Carbon::now();
        $month = $month ?? (int) $now->format('m');
        $year = $year ?? (int) $now->format('Y');

        if (!$useCache) {
            return $this->computeDashboardMetrics($user, $month, $year);
        }

        $version = Cache::get("user_{$user->id}_wealth_ver", 1);
        $cacheKey = "wealth_metrics_u{$user->id}_v{$version}_{$year}_{$month}";

        return Cache::remember($cacheKey, 600, function () use ($user, $month, $year) {
            return $this->computeDashboardMetrics($user, $month, $year);
        });
    }

    /**
     * Invalidate cached metrics for a user.
     */
    public static function clearUserCache(int $userId): void
    {
        $current = (int) Cache::get("user_{$userId}_wealth_ver", 1);
        Cache::put("user_{$userId}_wealth_ver", $current + 1, 86400 * 30);
    }

    /**
     * Get active categories for user with high-performance cache.
     */
    public function getActiveCategories(User $user): Collection
    {
        $version = (int) Cache::get("user_{$user->id}_cat_ver", 1);
        $cacheKey = "user_{$user->id}_categories_v{$version}";

        return Cache::remember($cacheKey, 3600, function () use ($user) {
            return Category::where(function ($q) use ($user) {
                $q->whereNull('user_id')->orWhere('user_id', $user->id);
            })->get()->map(fn ($cat) => [
                'id' => $cat->id,
                'name' => $cat->name,
                'type' => $cat->type,
                'icon' => $cat->icon,
                'color' => $cat->color,
            ]);
        });
    }

    /**
     * Invalidate cached categories.
     */
    public static function clearCategoryCache(?int $userId = null): void
    {
        if ($userId) {
            $current = (int) Cache::get("user_{$userId}_cat_ver", 1);
            Cache::put("user_{$userId}_cat_ver", $current + 1, 86400 * 30);
        }
    }

    /**
     * Compute financial metrics using consolidated single-query data processing.
     */
    public function computeDashboardMetrics(User $user, int $month, int $year): array
    {
        $now = Carbon::now();
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $endDate = $startDate->copy()->endOfMonth()->endOfDay();
        
        $daysInMonth = $startDate->daysInMonth;
        $isCurrentMonth = ($month === (int) $now->format('m') && $year === (int) $now->format('Y'));
        $todayDay = $isCurrentMonth ? (int) $now->format('d') : ($month < (int) $now->format('m') ? $daysInMonth : 1);
        $todayDateStr = $isCurrentMonth ? $now->format('Y-m-d') : $startDate->copy()->day($todayDay)->format('Y-m-d');
        $daysRemaining = max(1, $daysInMonth - $todayDay + 1);

        // 1. Single Indexed Fetch for ALL Month Transactions with Relations
        $monthTransactions = Transaction::with(['category', 'monthlyObligation'])
            ->where('user_id', $user->id)
            ->whereBetween('transaction_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get();

        // Income Metrics (calculated in-memory)
        $totalIncome = (float) $monthTransactions
            ->where('type', 'income')
            ->sum('amount');

        // 2. Monthly Obligations & Ring-Fencing
        $activeObligations = MonthlyObligation::with(['category'])
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->orderBy('due_day', 'asc')
            ->get();

        $totalObligationsAmount = (float) $activeObligations->sum('amount');

        // Fetch payment checklist for period
        $payments = MonthlyObligationPayment::where('user_id', $user->id)
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->get()
            ->keyBy('monthly_obligation_id');

        $obligationsChecklist = $activeObligations->map(function ($obligation) use ($payments) {
            $payment = $payments->get($obligation->id);
            return [
                'id' => $obligation->id,
                'name' => $obligation->name,
                'amount' => (float) $obligation->amount,
                'due_day' => $obligation->due_day,
                'category_name' => $obligation->category?->name ?? 'Kewajiban',
                'category_color' => $obligation->category?->color ?? '#F59E0B',
                'category_icon' => $obligation->category?->icon ?? 'Tag',
                'is_paid' => (bool) ($payment?->is_paid ?? false),
                'paid_amount' => (float) ($payment?->paid_amount ?? 0),
                'paid_at' => $payment?->paid_at?->format('d M Y, H:i') ?? null,
            ];
        });

        $totalPaidObligations = (float) $obligationsChecklist->where('is_paid', true)->sum('paid_amount');
        $totalReservedObligations = max(0, $totalObligationsAmount - $totalPaidObligations);

        // 3. Growth Target & Net Worth
        $growthTarget = GrowthTarget::where('user_id', $user->id)
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->first();

        $targetGrowthPercentage = (float) ($growthTarget?->target_growth_percentage ?? 5.00);

        // Saldo / uang yang dipunya pada awal bulan (dihitung dinamis dari saldo awal bawaan + seluruh transaksi sebelum bulan ini)
        $priorIncome = (float) Transaction::where('user_id', $user->id)
            ->where('transaction_date', '<', $startDate->format('Y-m-d'))
            ->where('type', 'income')
            ->sum('amount');

        $priorExpenses = (float) Transaction::where('user_id', $user->id)
            ->where('transaction_date', '<', $startDate->format('Y-m-d'))
            ->where('type', 'expense')
            ->sum('amount');

        $hasPriorTransactions = ($priorIncome > 0 || $priorExpenses > 0);
        $startingNetWorth = $hasPriorTransactions
            ? ((float) ($user->initial_net_worth ?? 0.0) + ($priorIncome - $priorExpenses))
            : (float) ($growthTarget?->starting_net_worth > 0 
                ? $growthTarget->starting_net_worth 
                : ($user->initial_net_worth ?? 0.0));
        
        // Target tabungan: menyesuaikan berdasarkan uang yang dipunya (saldo awal, atau total uang masuk)
        $baseWealthForGrowth = $startingNetWorth > 0 ? $startingNetWorth : max(0, $totalIncome);
        $calculatedGrowthAmount = ($baseWealthForGrowth > 0) ? ($baseWealthForGrowth * ($targetGrowthPercentage / 100)) : 0;
        $targetSavingsAmount = (float) ($growthTarget?->target_savings_amount > 0 ? $growthTarget->target_savings_amount : $calculatedGrowthAmount);

        // 4. Daily Expenses (Excluding obligations) calculated in-memory from $monthTransactions
        $pastExpenses = 0.0;
        $spentToday = 0.0;
        $totalDailyExpensesThisMonth = 0.0;

        foreach ($monthTransactions as $tx) {
            if ($tx->type === 'expense' && is_null($tx->monthly_obligation_id)) {
                $txDateStr = $tx->transaction_date instanceof Carbon
                    ? $tx->transaction_date->format('Y-m-d')
                    : (string) $tx->transaction_date;

                $amt = (float) $tx->amount;
                $totalDailyExpensesThisMonth += $amt;

                if ($txDateStr < $todayDateStr) {
                    $pastExpenses += $amt;
                } elseif ($txDateStr === $todayDateStr) {
                    $spentToday += $amt;
                }
            }
        }

        // Total all expenses (Daily + Paid Obligations)
        $totalAllExpenses = $totalDailyExpensesThisMonth + $totalPaidObligations;

        // 5. Dynamic Daily Budgeting & Rollover Calculation
        $basePool = max(0, $totalIncome - $totalObligationsAmount - $targetSavingsAmount);
        $baselineDailyAllowance = $daysInMonth > 0 ? ($basePool / $daysInMonth) : 0;

        // Remaining pool for today and upcoming days (auto mode based on money owned)
        $remainingPool = max(0, $totalIncome - $totalObligationsAmount - $targetSavingsAmount - $pastExpenses);
        $autoDailyBudget = $daysRemaining > 0 ? ($remainingPool / $daysRemaining) : 0;

        $dailyBudgetMode = $user->daily_budget_mode ?? 'auto';
        $manualDailyBudget = (float) ($user->manual_daily_budget ?? 0);

        if ($dailyBudgetMode === 'manual' && $manualDailyBudget > 0) {
            $todayDailyBudget = $manualDailyBudget;
            $rolloverDelta = 0.0;
        } else {
            $todayDailyBudget = $autoDailyBudget;
            $rolloverDelta = $todayDailyBudget - $baselineDailyAllowance;
        }
        
        $todayRemaining = $todayDailyBudget - $spentToday;
        $todayProgressPercentage = ($todayDailyBudget > 0) ? min(200, round(($spentToday / $todayDailyBudget) * 100, 1)) : ($spentToday > 0 ? 100 : 0);

        // 6. Net Worth & Growth Status
        $currentNetWorth = $startingNetWorth + ($totalIncome - $totalAllExpenses);
        
        // Projected End of Month Net Worth based on active income and committed obligations & daily expenses:
        $projectedEndOfMonthNetWorth = $startingNetWorth + $totalIncome - $totalObligationsAmount - $totalDailyExpensesThisMonth;
        
        $currentGrowthPercentage = ($startingNetWorth > 0) 
            ? round((($currentNetWorth - $startingNetWorth) / $startingNetWorth) * 100, 2)
            : 0;

        $projectedGrowthPercentage = ($startingNetWorth > 0)
            ? round((($projectedEndOfMonthNetWorth - $startingNetWorth) / $startingNetWorth) * 100, 2)
            : 0;

        $targetRequiredNetWorth = $startingNetWorth + $targetSavingsAmount;
        $isGrowthOnTrack = ($targetSavingsAmount > 0) 
            ? ($projectedEndOfMonthNetWorth >= $targetRequiredNetWorth)
            : ($projectedGrowthPercentage >= $targetGrowthPercentage);

        // 7. Recent Transactions (last 10 indexed fetch)
        $recentTransactions = Transaction::with(['category', 'monthlyObligation'])
            ->where('user_id', $user->id)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get()
            ->map(fn ($tx) => $this->formatTransaction($tx));

        // 8. Visual Charts Data (O(1) in-memory aggregation without duplicate queries)
        $growthChart = $this->generateGrowthChartData(
            $startDate, 
            $daysInMonth, 
            $todayDay, 
            $startingNetWorth, 
            $targetSavingsAmount, 
            $monthTransactions
        );
        $categoryBreakdown = $this->generateCategoryBreakdown($monthTransactions);

        return [
            'period' => [
                'month' => $month,
                'year' => $year,
                'month_name' => $startDate->translatedFormat('F Y'),
                'today_day' => $todayDay,
                'days_in_month' => $daysInMonth,
                'days_remaining' => $daysRemaining,
                'is_current_month' => $isCurrentMonth,
            ],
            'cashflow' => [
                'total_income' => $totalIncome,
                'total_expenses' => $totalAllExpenses,
                'net_cashflow' => $totalIncome - $totalAllExpenses,
                'savings_target' => $targetSavingsAmount,
                'projected_surplus' => max(0, $totalIncome - $totalObligationsAmount - $totalDailyExpensesThisMonth),
            ],
            'obligations' => [
                'total_amount' => $totalObligationsAmount,
                'paid_amount' => $totalPaidObligations,
                'reserved_amount' => $totalReservedObligations,
                'count' => $activeObligations->count(),
                'paid_count' => $obligationsChecklist->where('is_paid', true)->count(),
                'checklist' => $obligationsChecklist->values()->all(),
            ],
            'daily_budget' => [
                'mode' => $dailyBudgetMode,
                'manual_budget' => round($manualDailyBudget, 2),
                'auto_budget' => round($autoDailyBudget, 2),
                'today_budget' => round($todayDailyBudget, 2),
                'spent_today' => round($spentToday, 2),
                'remaining_today' => round($todayRemaining, 2),
                'progress_percentage' => $todayProgressPercentage,
                'status' => $todayRemaining < 0 ? 'overbudget' : ($todayProgressPercentage >= 70 ? 'warning' : 'safe'),
                'baseline_allowance' => round($baselineDailyAllowance, 2),
                'rollover_delta' => round($rolloverDelta, 2),
                'rollover_type' => $rolloverDelta >= 0 ? 'reward' : 'adjusted',
                'past_expenses' => round($pastExpenses, 2),
            ],
            'growth' => [
                'starting_net_worth' => $startingNetWorth,
                'current_net_worth' => $currentNetWorth,
                'projected_net_worth' => $projectedEndOfMonthNetWorth,
                'target_percentage' => $targetGrowthPercentage,
                'current_percentage' => $currentGrowthPercentage,
                'projected_percentage' => $projectedGrowthPercentage,
                'is_on_track' => $isGrowthOnTrack,
                'target_savings_amount' => $targetSavingsAmount,
                'gap_amount' => round(max(0, $targetRequiredNetWorth - $projectedEndOfMonthNetWorth), 2),
            ],
            'recent_transactions' => $recentTransactions,
            'charts' => [
                'growth' => $growthChart,
                'categories' => $categoryBreakdown,
            ],
        ];
    }

    /**
     * Pre-check whether an expense of given amount will jeopardize the monthly wealth growth target.
     */
    public function checkGrowthIntervention(User $user, float $amount, ?string $date = null): array
    {
        $txDate = $date ? Carbon::parse($date) : Carbon::now();
        $month = (int) $txDate->format('m');
        $year = (int) $txDate->format('Y');

        $metrics = $this->getDashboardMetrics($user, $month, $year);
        $growth = $metrics['growth'];
        $startingNetWorth = $growth['starting_net_worth'];
        $targetPercentage = $growth['target_percentage'];
        $targetSavingsAmount = (float) ($growth['target_savings_amount'] ?? 0);
        
        // Calculate newly projected net worth after this expense
        $newProjectedNetWorth = $growth['projected_net_worth'] - $amount;
        $newProjectedPercentage = ($startingNetWorth > 0)
            ? round((($newProjectedNetWorth - $startingNetWorth) / $startingNetWorth) * 100, 2)
            : 0;

        $targetRequiredNetWorth = $startingNetWorth + $targetSavingsAmount;
        $breaches = ($targetSavingsAmount > 0)
            ? ($newProjectedNetWorth < $targetRequiredNetWorth)
            : ($newProjectedPercentage < $targetPercentage);
        $growthDrop = round($growth['projected_percentage'] - $newProjectedPercentage, 2);

        return [
            'breaches_target' => $breaches,
            'current_projected_percentage' => $growth['projected_percentage'],
            'new_projected_percentage' => $newProjectedPercentage,
            'target_percentage' => $targetPercentage,
            'growth_drop_points' => $growthDrop,
            'amount' => $amount,
            'current_projected_net_worth' => $growth['projected_net_worth'],
            'new_projected_net_worth' => $newProjectedNetWorth,
            'warning_message' => $breaches
                ? "Peringatan: Pengeluaran sebesar Rp " . number_format($amount, 0, ',', '.') . " ini akan membuat target tabungan/pertumbuhan kekayaan turun menjadi {$newProjectedPercentage}% (di bawah target {$targetPercentage}% Anda)."
                : null,
        ];
    }

    /**
     * Generate daily cumulative net worth curve data using O(1) hashmap lookup.
     */
    private function generateGrowthChartData(
        Carbon $startDate, 
        int $daysInMonth,
        int $todayDay, 
        float $startingNetWorth, 
        float $targetSavingsAmount,
        Collection $monthTransactions
    ): array {
        $labels = [];
        $actualNetWorth = [];
        $targetTrend = [];

        // Single-pass hashmap: aggregate income & expense by date string in O(N)
        $dailyDeltas = [];
        foreach ($monthTransactions as $tx) {
            $d = $tx->transaction_date instanceof Carbon 
                ? $tx->transaction_date->format('Y-m-d') 
                : (string) $tx->transaction_date;
            
            if (!isset($dailyDeltas[$d])) {
                $dailyDeltas[$d] = 0.0;
            }

            if ($tx->type === 'income') {
                $dailyDeltas[$d] += (float) $tx->amount;
            } elseif ($tx->type === 'expense') {
                $dailyDeltas[$d] -= (float) $tx->amount;
            }
        }

        $runningNetWorth = $startingNetWorth;
        $targetPerDay = $targetSavingsAmount / max(1, $daysInMonth);

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $currentDateStr = $startDate->copy()->day($day)->format('Y-m-d');
            $labels[] = "Tgl {$day}";

            $targetTrend[] = round($startingNetWorth + ($targetPerDay * $day), 0);

            if ($day <= $todayDay) {
                $runningNetWorth += ($dailyDeltas[$currentDateStr] ?? 0.0);
                $actualNetWorth[] = round($runningNetWorth, 0);
            } else {
                $actualNetWorth[] = null;
            }
        }

        return [
            'labels' => $labels,
            'actual' => $actualNetWorth,
            'target' => $targetTrend,
        ];
    }

    /**
     * Generate category distribution breakdown from already-loaded month transactions (Zero DB queries).
     */
    private function generateCategoryBreakdown(Collection $monthTransactions): array
    {
        $expenses = $monthTransactions->where('type', 'expense');

        $grouped = $expenses->groupBy(function ($tx) {
            return $tx->category?->name ?? 'Lain-lain';
        });

        $totalExpense = (float) $expenses->sum('amount');
        $data = [];

        foreach ($grouped as $catName => $txList) {
            $catAmount = (float) $txList->sum('amount');
            $category = $txList->first()->category;
            $data[] = [
                'name' => $catName,
                'amount' => $catAmount,
                'percentage' => $totalExpense > 0 ? round(($catAmount / $totalExpense) * 100, 1) : 0,
                'color' => $category?->color ?? '#64748B',
                'icon' => $category?->icon ?? 'Tag',
                'count' => $txList->count(),
            ];
        }

        usort($data, fn ($a, $b) => $b['amount'] <=> $a['amount']);

        return [
            'total' => $totalExpense,
            'items' => $data,
        ];
    }

    /**
     * Formatter for individual transaction response.
     */
    public function formatTransaction(Transaction $transaction): array
    {
        return [
            'id' => $transaction->id,
            'type' => $transaction->type,
            'amount' => (float) $transaction->amount,
            'transaction_date' => $transaction->transaction_date?->format('Y-m-d'),
            'formatted_date' => $transaction->transaction_date?->translatedFormat('d M Y'),
            'description' => $transaction->description,
            'payment_method' => $transaction->payment_method,
            'is_growth_overridden' => (bool) $transaction->is_growth_overridden,
            'category' => $transaction->category ? [
                'id' => $transaction->category->id,
                'name' => $transaction->category->name,
                'type' => $transaction->category->type,
                'icon' => $transaction->category->icon,
                'color' => $transaction->category->color,
            ] : null,
            'obligation' => $transaction->monthlyObligation ? [
                'id' => $transaction->monthlyObligation->id,
                'name' => $transaction->monthlyObligation->name,
            ] : null,
        ];
    }
}
