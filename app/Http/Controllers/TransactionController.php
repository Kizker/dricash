<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use App\Services\WealthPlannerService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TransactionController extends Controller
{
    public function __construct(
        protected WealthPlannerService $wealthService
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $query = Transaction::with(['category', 'monthlyObligation'])
            ->where('user_id', $user->id);

        // Search Filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('payment_method', 'like', "%{$search}%")
                  ->orWhereHas('category', fn ($cat) => $cat->where('name', 'like', "%{$search}%"));
            });
        }

        // Type Filter
        if ($request->filled('type') && in_array($request->input('type'), ['income', 'expense'])) {
            $query->where('type', $request->input('type'));
        }

        // Category Filter
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        // Date Range / Preset Filter
        if ($request->filled('date_from')) {
            $query->where('transaction_date', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->where('transaction_date', '<=', $request->input('date_to'));
        }

        // Default to current month if no dates are provided (SARGable range seek)
        if (!$request->filled('date_from') && !$request->filled('date_to') && !$request->filled('search')) {
            $now = Carbon::now();
            $month = (int) $request->input('month', (int) $now->format('m'));
            $year = (int) $request->input('year', (int) $now->format('Y'));
            $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfDay()->format('Y-m-d');
            $endOfMonth = Carbon::createFromDate($year, $month, 1)->endOfMonth()->endOfDay()->format('Y-m-d');
            $query->whereBetween('transaction_date', [$startOfMonth, $endOfMonth]);
        }

        $transactions = $query->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString()
            ->through(fn ($tx) => $this->wealthService->formatTransaction($tx));

        $categories = $this->wealthService->getActiveCategories($user);

        return Inertia::render('Transactions/Index', [
            'transactions' => $transactions,
            'categories' => $categories,
            'filters' => $request->only(['search', 'type', 'category_id', 'date_from', 'date_to', 'month', 'year']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:income,expense'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'category_id' => ['required', 'exists:categories,id'],
            'transaction_date' => ['required', 'date'],
            'description' => ['required', 'string', 'max:255'],
            'payment_method' => ['nullable', 'string', 'max:100'],
        ]);

        $user = $request->user();

        Transaction::create([
            'user_id' => $user->id,
            'category_id' => $validated['category_id'],
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'transaction_date' => $validated['transaction_date'],
            'description' => $validated['description'],
            'payment_method' => $validated['payment_method'] ?? 'Transfer',
        ]);

        return back()->with('success', 'Transaksi berhasil ditambahkan ke buku besar.');
    }

    public function update(Request $request, Transaction $transaction): RedirectResponse
    {
        if ($transaction->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'type' => ['required', 'in:income,expense'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'category_id' => ['required', 'exists:categories,id'],
            'transaction_date' => ['required', 'date'],
            'description' => ['required', 'string', 'max:255'],
            'payment_method' => ['nullable', 'string', 'max:100'],
        ]);

        $transaction->update($validated);

        return back()->with('success', 'Transaksi berhasil diperbarui.');
    }

    public function destroy(Request $request, Transaction $transaction): RedirectResponse
    {
        if ($transaction->user_id !== $request->user()->id) {
            abort(403);
        }

        $transaction->delete();

        return back()->with('success', 'Transaksi berhasil dihapus dari buku besar.');
    }
}
