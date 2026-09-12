<?php

namespace App\Http\Controllers;

use App\Services\WealthPlannerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DailyBudgetController extends Controller
{
    /**
     * Update user daily budget calculation preference (auto vs manual).
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'daily_budget_mode' => ['required', 'string', 'in:auto,manual'],
            'manual_daily_budget' => ['nullable', 'numeric', 'min:0'],
        ]);

        $user = $request->user();

        $user->update([
            'daily_budget_mode' => $validated['daily_budget_mode'],
            'manual_daily_budget' => $validated['daily_budget_mode'] === 'manual'
                ? ($validated['manual_daily_budget'] ?? 0)
                : null,
        ]);

        WealthPlannerService::clearUserCache($user->id);

        return back()->with('success', 'Pengaturan jatah harian berhasil disimpan.');
    }
}
