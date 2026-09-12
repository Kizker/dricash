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

        $categories = Category::where(function ($q) use ($user) {
            $q->whereNull('user_id')->orWhere('user_id', $user->id);
        })->get()->map(fn ($cat) => [
            'id' => $cat->id,
            'name' => $cat->name,
            'type' => $cat->type,
            'icon' => $cat->icon,
            'color' => $cat->color,
        ]);

        $obligations = MonthlyObligation::with(['category'])
            ->where('user_id', $user->id)
            ->orderBy('due_day', 'asc')
            ->get()
            ->map(function ($ob) {
                return [
                    'id' => $ob->id,
                    'category_id' => $ob->category_id,
                    'name' => $ob->name,
                    'amount' => (float) $ob->amount,
                    'due_day' => $ob->due_day,
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
            'notes' => ['nullable', 'string'],
        ]);

        $user = $request->user();
        MonthlyObligation::create([
            'user_id' => $user->id,
            'category_id' => $validated['category_id'] ?? null,
            'name' => $validated['name'],
            'amount' => $validated['amount'],
            'due_day' => $validated['due_day'],
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
                    'payment_method' => 'Transfer',
                ]
            );

            $msg = "'{$obligation->name}' ditandai LUNAS dan dicatat ke pengeluaran.";
        } else {
            $payment->is_paid = false;
            $payment->paid_amount = 0.00;
            $payment->paid_at = null;
            $payment->save();

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
