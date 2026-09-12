<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\GrowthTarget;
use App\Models\MonthlyObligation;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();

        // 1. User Metrics
        $totalUsers = User::count();
        $activeUsers = User::where('is_active', true)->count();
        $suspendedUsers = User::where('is_active', false)->count();
        $newUsersThisMonth = User::where('created_at', '>=', $startOfMonth)->count();
        $newUsersLastMonth = User::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->count();
        
        $userGrowthPercentage = $newUsersLastMonth > 0
            ? round((($newUsersThisMonth - $newUsersLastMonth) / $newUsersLastMonth) * 100, 1)
            : ($newUsersThisMonth > 0 ? 100 : 0);

        // 2. Financial Platform Metrics
        $totalTransactions = Transaction::count();
        $totalPlatformInflow = (float) Transaction::where('type', 'income')->sum('amount');
        $totalPlatformOutflow = (float) Transaction::where('type', 'expense')->sum('amount');
        
        $thisMonthInflow = (float) Transaction::where('type', 'income')
            ->where('transaction_date', '>=', $startOfMonth->format('Y-m-d'))
            ->sum('amount');
            
        $thisMonthOutflow = (float) Transaction::where('type', 'expense')
            ->where('transaction_date', '>=', $startOfMonth->format('Y-m-d'))
            ->sum('amount');

        // 3. Obligations & System Master Metrics
        $totalActiveObligations = MonthlyObligation::where('is_active', true)->count();
        $totalRingFencedMonthly = (float) MonthlyObligation::where('is_active', true)->sum('amount');
        $totalGrowthTargets = GrowthTarget::count();
        $totalDefaultCategories = Category::where('is_default', true)->count();

        // 4. Trend Data for Charts (Last 6 Months)
        $monthsTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthDate = $now->copy()->subMonths($i);
            $mStart = $monthDate->copy()->startOfMonth();
            $mEnd = $monthDate->copy()->endOfMonth();
            $mLabel = $monthDate->translatedFormat('M Y');

            $inflow = (float) Transaction::where('type', 'income')
                ->whereBetween('transaction_date', [$mStart->format('Y-m-d'), $mEnd->format('Y-m-d')])
                ->sum('amount');

            $outflow = (float) Transaction::where('type', 'expense')
                ->whereBetween('transaction_date', [$mStart->format('Y-m-d'), $mEnd->format('Y-m-d')])
                ->sum('amount');

            $userCount = User::where('created_at', '<=', $mEnd)->count();
            $newRegistrations = User::whereBetween('created_at', [$mStart, $mEnd])->count();

            $monthsTrend[] = [
                'label' => $mLabel,
                'inflow' => $inflow,
                'outflow' => $outflow,
                'net' => $inflow - $outflow,
                'total_users' => $userCount,
                'new_users' => $newRegistrations,
            ];
        }

        // 5. Recent System Activities
        $recentUsers = User::latest()
            ->take(5)
            ->get(['id', 'name', 'email', 'role', 'is_active', 'created_at']);

        $recentTransactions = Transaction::with(['user:id,name', 'category:id,name,color'])
            ->latest('transaction_date')
            ->latest('id')
            ->take(6)
            ->get(['id', 'user_id', 'category_id', 'type', 'amount', 'description', 'transaction_date']);

        return Inertia::render('Admin/Dashboard', [
            'metrics' => [
                'users' => [
                    'total' => $totalUsers,
                    'active' => $activeUsers,
                    'suspended' => $suspendedUsers,
                    'new_this_month' => $newUsersThisMonth,
                    'growth_rate' => $userGrowthPercentage,
                ],
                'finances' => [
                    'total_transactions' => $totalTransactions,
                    'total_inflow' => $totalPlatformInflow,
                    'total_outflow' => $totalPlatformOutflow,
                    'this_month_inflow' => $thisMonthInflow,
                    'this_month_outflow' => $thisMonthOutflow,
                ],
                'obligations' => [
                    'active_count' => $totalActiveObligations,
                    'total_ring_fenced' => $totalRingFencedMonthly,
                ],
                'system' => [
                    'growth_targets' => $totalGrowthTargets,
                    'default_categories' => $totalDefaultCategories,
                ],
            ],
            'chart_data' => $monthsTrend,
            'recent_users' => $recentUsers,
            'recent_transactions' => $recentTransactions,
        ]);
    }
}
