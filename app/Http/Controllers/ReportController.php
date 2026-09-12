<?php

namespace App\Http\Controllers;

use App\Services\WealthPlannerService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
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

        return Inertia::render('Reports/Index', [
            'metrics' => $metrics,
            'period' => $metrics['period'],
            'cashflow' => $metrics['cashflow'],
            'obligations' => $metrics['obligations'],
            'daily_budget' => $metrics['daily_budget'],
            'growth' => $metrics['growth'],
            'charts' => $metrics['charts'],
        ]);
    }
}
