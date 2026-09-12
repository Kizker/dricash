<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use App\Services\WealthPlannerService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class QuickEntryController extends Controller
{
    public function __construct(
        protected WealthPlannerService $wealthService
    ) {}

    /**
     * Pre-check whether spending will breach wealth target.
     */
    public function checkIntervention(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'date' => ['nullable', 'date'],
        ]);

        $result = $this->wealthService->checkGrowthIntervention(
            $request->user(),
            (float) $validated['amount'],
            $validated['date'] ?? null
        );

        return response()->json($result);
    }

    /**
     * Store new quick income or expense transaction.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:income,expense'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'category_id' => ['required', 'exists:categories,id'],
            'transaction_date' => ['required', 'date'],
            'description' => ['required', 'string', 'max:255'],
            'payment_method' => ['nullable', 'string', Rule::in(Transaction::PAYMENT_METHODS)],
            'force_override' => ['nullable', 'boolean'],
        ]);

        $user = $request->user();
        $isExpense = ($validated['type'] === 'expense');
        $forceOverride = $request->boolean('force_override');

        // If it's an expense and not forced, check intervention
        if ($isExpense && !$forceOverride) {
            $intervention = $this->wealthService->checkGrowthIntervention(
                $user,
                (float) $validated['amount'],
                $validated['transaction_date']
            );

            if ($intervention['breaches_target']) {
                return back()->with('intervention', [
                    'amount' => (float) $validated['amount'],
                    'message' => $intervention['warning_message'],
                    'new_projected_percentage' => $intervention['new_projected_percentage'],
                    'target_percentage' => $intervention['target_percentage'],
                    'payload' => $validated,
                ]);
            }
        }

        Transaction::create([
            'user_id' => $user->id,
            'category_id' => $validated['category_id'],
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'transaction_date' => $validated['transaction_date'],
            'description' => $validated['description'],
            'payment_method' => $validated['payment_method'] ?? 'Bank',
            'is_growth_overridden' => $forceOverride,
        ]);

        $typeLabel = $isExpense ? 'Pengeluaran' : 'Pemasukan';
        $formattedAmount = 'Rp ' . number_format($validated['amount'], 0, ',', '.');

        return back()->with('success', "{$typeLabel} sebesar {$formattedAmount} berhasil dicatat!");
    }
}
