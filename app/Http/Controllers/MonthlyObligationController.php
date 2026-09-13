<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\MonthlyObligation;
use App\Models\MonthlyObligationPayment;
use App\Models\Transaction;
use App\Services\WealthPlannerService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MonthlyObligationController extends Controller
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
        $categories = $this->wealthService->getActiveCategories($user);

        $obligations = MonthlyObligation::with(['category'])
            ->where('user_id', $user->id)
            ->orderBy('due_day', 'asc')
            ->get()
            ->map(function ($ob) {
                $totalInst = $ob->total_installments ? (int) $ob->total_installments : null;
                $paidInst = (int) ($ob->paid_installments ?? 0);
                $remainingInst = $totalInst ? max(0, $totalInst - $paidInst) : null;
                $remainingAmount = $totalInst ? ($remainingInst * (float) $ob->amount) : null;

                return [
                    'id' => $ob->id,
                    'category_id' => $ob->category_id,
                    'name' => $ob->name,
                    'amount' => (float) $ob->amount,
                    'due_day' => $ob->due_day,
                    'total_installments' => $totalInst,
                    'paid_installments' => $paidInst,
                    'remaining_installments' => $remainingInst,
                    'remaining_amount' => $remainingAmount,
                    'is_installment' => !is_null($totalInst) || (str_contains(strtolower($ob->category?->name ?? ''), 'cicilan')),
                    'is_active' => (bool) $ob->is_active,
                    'notes' => $ob->notes,
                    'category' => $ob->category ? [
                        'id' => $ob->category->id,
                        'name' => $ob->category->name,
                        'icon' => $ob->category->icon,
                        'color' => $ob->category->color,
                    ] : null,
                ];
            });

        return Inertia::render('Obligations/Index', [
            'obligations' => $obligations,
            'categories' => $categories,
            'metrics' => $metrics['obligations'],
            'wealth' => $metrics['growth'],
            'period' => $metrics['period'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'due_day' => ['required', 'integer', 'min:1', 'max:31'],
            'total_installments' => ['nullable', 'integer', 'min:1', 'max:360'],
            'paid_installments' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $user = $request->user();
        MonthlyObligation::create([
            'user_id' => $user->id,
            'category_id' => $validated['category_id'] ?? null,
            'name' => $validated['name'],
            'amount' => $validated['amount'],
            'due_day' => $validated['due_day'],
            'total_installments' => $validated['total_installments'] ?? null,
            'paid_installments' => $validated['paid_installments'] ?? 0,
            'is_active' => true,
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('success', "Kebutuhan bulanan '{$validated['name']}' berhasil ditambahkan ke sistem Ring-Fencing.");
    }

    public function update(Request $request, MonthlyObligation $obligation): RedirectResponse
    {
        if ($obligation->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'due_day' => ['required', 'integer', 'min:1', 'max:31'],
            'total_installments' => ['nullable', 'integer', 'min:1', 'max:360'],
            'paid_installments' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $obligation->update($validated);

        return back()->with('success', "Kebutuhan bulanan '{$obligation->name}' berhasil diperbarui.");
    }

    public function destroy(Request $request, MonthlyObligation $obligation): RedirectResponse
    {
        if ($obligation->user_id !== $request->user()->id) {
            abort(403);
        }

        $obligation->delete();

        return back()->with('success', "Kebutuhan bulanan '{$obligation->name}' berhasil dihapus.");
    }

    /**
     * Toggle payment status checklist for a specific month/year.
     */
    public function togglePayment(Request $request, MonthlyObligation $obligation): RedirectResponse
    {
        if ($obligation->user_id !== $request->user()->id) {
            abort(403);
        }

        $now = Carbon::now();
        $month = $request->filled('month') ? (int) $request->input('month') : (int) $now->format('m');
        $year = $request->filled('year') ? (int) $request->input('year') : (int) $now->format('Y');

        $payment = MonthlyObligationPayment::firstOrNew([
            'user_id' => $request->user()->id,
            'monthly_obligation_id' => $obligation->id,
            'period_month' => $month,
            'period_year' => $year,
        ]);

        $newStatus = !$payment->is_paid;

        if ($newStatus) {
            $payment->is_paid = true;
            $payment->paid_amount = $obligation->amount;
            $payment->paid_at = Carbon::now();
            $payment->save();

            // Increment paid_installments jika ada target total_installments
            if ($obligation->total_installments && $obligation->paid_installments < $obligation->total_installments) {
                $obligation->increment('paid_installments');
            }

            // Create expense transaction
            Transaction::updateOrCreate(
                [
                    'user_id' => $request->user()->id,
                    'monthly_obligation_id' => $obligation->id,
                    'transaction_date' => Carbon::now()->format('Y-m-d'),
                ],
                [
                    'category_id' => $obligation->category_id,
                    'type' => 'expense',
                    'amount' => $obligation->amount,
                    'description' => "Pembayaran Kewajiban: {$obligation->name}",
                    'payment_method' => 'Bank',
                ]
            );

            $msg = "'{$obligation->name}' ditandai LUNAS dan dicatat ke pengeluaran.";
        } else {
            $payment->is_paid = false;
            $payment->paid_amount = 0.00;
            $payment->paid_at = null;
            $payment->save();

            // Decrement paid_installments jika sebelumnya bertambah
            if ($obligation->total_installments && $obligation->paid_installments > 0) {
                $obligation->decrement('paid_installments');
            }

            // Remove corresponding transaction for this period
            Transaction::where('user_id', $request->user()->id)
                ->where('monthly_obligation_id', $obligation->id)
                ->whereMonth('transaction_date', $month)
                ->whereYear('transaction_date', $year)
                ->delete();

            $msg = "'{$obligation->name}' dikembalikan ke status BELUM BAYAR (Dana Terkunci).";
        }

        return back()->with('success', $msg);
    }
}
