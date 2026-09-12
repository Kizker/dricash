<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\WealthPlannerService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        protected WealthPlannerService $wealthService
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $month = $request->filled('month') ? (int) $request->input('month') : null;
        $year = $request->filled('year') ? (int) $request->input('year') : null;

        $metrics = $this->wealthService->getDashboardMetrics($user, $month, $year);

        // Fetch active categories for Quick Entry dropdowns
        $categories = Category::where(function ($q) use ($user) {
            $q->whereNull('user_id')->orWhere('user_id', $user->id);
        })->get()->map(fn ($cat) => [
            'id' => $cat->id,
            'name' => $cat->name,
            'type' => $cat->type,
            'icon' => $cat->icon,
            'color' => $cat->color,
        ]);

        return Inertia::render('Dashboard', [
            'metrics' => $metrics,
            'categories' => $categories,
        ]);
    }
}
