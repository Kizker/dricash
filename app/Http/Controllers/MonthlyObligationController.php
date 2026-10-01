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

        // Fetch payment records for this specific month & year
        $payments = MonthlyObligationPayment::where('user_id', $user->id)
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->get()
            ->keyBy('monthly_obligation_id');

        $periodKey = $year * 12 + $month;

        $obligations = MonthlyObligation::with(['category'])
            ->where('user_id', $user->id)
            ->get()
            ->map(function ($ob) use ($payments, $periodKey) {
                $payment = $payments->get($ob->id);
                $isPaid = (bool) ($payment?->is_paid ?? false);

                $totalInst = $ob->total_installments ? (int) $ob->total_installments : null;
                $paidInst = (int) ($ob->paid_installments ?? 0);
                $remainingInst = $totalInst ? max(0, $totalInst - $paidInst) : null;
                $remainingAmount = $totalInst ? ($remainingInst * (float) $ob->amount) : null;

                $startKey = ($ob->start_year && $ob->start_month) ? ($ob->start_year * 12 + $ob->start_month) : null;
                $endKey = ($ob->end_year && $ob->end_month) ? ($ob->end_year * 12 + $ob->end_month) : null;
                $isWithinPeriod = (!$startKey || $periodKey >= $startKey) && (!$endKey || $periodKey <= $endKey);
                $isBeforeStart = $startKey && ($periodKey < $startKey);
                $isAfterEnd = $endKey && ($periodKey > $endKey);

                $periodRange = null;
                if ($ob->start_month && $ob->start_year && $ob->end_month && $ob->end_year) {
                    $startFormatted = Carbon::createFromDate($ob->start_year, $ob->start_month, 1)->translatedFormat('M Y');
                    $endFormatted = Carbon::createFromDate($ob->end_year, $ob->end_month, 1)->translatedFormat('M Y');
                    $periodRange = "{$startFormatted} - {$endFormatted}";
                }

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
                    'start_month' => $ob->start_month,
                    'start_year' => $ob->start_year,
                    'end_month' => $ob->end_month,
                    'end_year' => $ob->end_year,
                    'period_range' => $periodRange,
                    'is_within_period' => $isWithinPeriod,
                    'is_before_start' => $isBeforeStart,
                    'is_after_end' => $isAfterEnd,
                    'is_installment' => !is_null($totalInst) || !is_null($ob->start_month) || (str_contains(strtolower($ob->category?->name ?? ''), 'cicilan')),
                    'is_active' => (bool) $ob->is_active,
                    'notes' => $ob->notes,
                    'is_paid' => $isPaid,
                    'paid_at' => $payment?->paid_at ? $payment->paid_at->translatedFormat('d M Y') : null,
                    'paid_time' => $payment?->paid_at ? $payment->paid_at->format('H:i') : null,
                    'paid_date' => $payment?->paid_at ? $payment->paid_at->format('Y-m-d') : null,
                    'category' => $ob->category ? [
                        'id' => $ob->category->id,
                        'name' => $ob->category->name,
                        'icon' => $ob->category->icon,
                        'color' => $ob->category->color,
                    ] : null,
                ];
            })
            ->sort(function ($a, $b) use ($year, $month) {
                // Tier 1: Belum lunas & Aktif periode ini (paling mendesak / terdekat)
                // Tier 2: Belum lunas & Periode mendatang (belum mulai)
                // Tier 3: Sudah lunas periode ini (pindah ke bawah)
                // Tier 4: Selesai / telah lewat masa periode
                $getRank = function ($item) {
                    if ($item['is_paid']) return 3;
                    if ($item['is_after_end']) return 4;
                    if ($item['is_within_period']) return 1;
                    if ($item['is_before_start']) return 2;
                    return 1;
                };

                $rankA = $getRank($a);
                $rankB = $getRank($b);
                if ($rankA !== $rankB) {
                    return $rankA <=> $rankB;
                }

                // Hitung key tanggal jatuh tempo berikutnya
                $getDueDateKey = function ($item) use ($year, $month) {
                    $dueDay = (int) ($item['due_day'] ?? 1);
                    if ($item['is_within_period']) {
                        return $year * 10000 + $month * 100 + $dueDay;
                    }
                    if ($item['is_before_start'] && $item['start_year'] && $item['start_month']) {
                        return (int) $item['start_year'] * 10000 + (int) $item['start_month'] * 100 + $dueDay;
                    }
                    if ($item['is_after_end'] && $item['end_year'] && $item['end_month']) {
                        return (int) $item['end_year'] * 10000 + (int) $item['end_month'] * 100 + $dueDay;
                    }
                    return $year * 10000 + $month * 100 + $dueDay;
                };

                $dateA = $getDueDateKey($a);
                $dateB = $getDueDateKey($b);
                if ($dateA !== $dateB) {
                    return $dateA <=> $dateB;
                }

                return ($a['id'] ?? 0) <=> ($b['id'] ?? 0);
            })
            ->values();

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
            'start_month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'start_year' => ['nullable', 'integer', 'min:2020', 'max:2099'],
            'end_month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'end_year' => ['nullable', 'integer', 'min:2020', 'max:2099'],
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
            'start_month' => $validated['start_month'] ?? null,
            'start_year' => $validated['start_year'] ?? null,
            'end_month' => $validated['end_month'] ?? null,
            'end_year' => $validated['end_year'] ?? null,
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
            'start_month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'start_year' => ['nullable', 'integer', 'min:2020', 'max:2099'],
            'end_month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'end_year' => ['nullable', 'integer', 'min:2020', 'max:2099'],
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
                $obligation->refresh();
            }

            // Hitung tanggal transaksi sesuai periode bulan & tanggal jatuh tempo yang ditandai
            $daysInPeriodMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;
            $targetDay = min((int) $obligation->due_day, $daysInPeriodMonth);
            $txDate = ($month === (int) $now->format('m') && $year === (int) $now->format('Y'))
                ? $now->format('Y-m-d')
                : Carbon::createFromDate($year, $month, $targetDay)->format('Y-m-d');

            // Format deskripsi transaksi dengan nomor cicilan bulanan
            if ($obligation->total_installments) {
                $txDescription = "Pembayaran {$obligation->name} (Cicilan ke-{$obligation->paid_installments} dari {$obligation->total_installments}x)";
            } else {
                $txDescription = "Pembayaran Kewajiban: {$obligation->name}";
            }

            // Tentukan kategori (fallback ke Cicilan & Pinjaman jika belum terpasang)
            $catId = $obligation->category_id;
            if (!$catId) {
                $catId = Category::where('user_id', $request->user()->id)
                    ->where(function ($q) {
                        $q->where('name', 'like', '%cicil%')
                          ->orWhere('name', 'like', '%pinjam%')
                          ->orWhere('name', 'like', '%kewajiban%');
                    })
                    ->value('id');
            }

            // Create or update expense transaction for this obligation and month/year
            $tx = Transaction::where('user_id', $request->user()->id)
                ->where('monthly_obligation_id', $obligation->id)
                ->whereMonth('transaction_date', $month)
                ->whereYear('transaction_date', $year)
                ->first();

            if ($tx) {
                $tx->update([
                    'category_id' => $catId,
                    'type' => 'expense',
                    'amount' => $obligation->amount,
                    'description' => $txDescription,
                    'payment_method' => $tx->payment_method ?: 'Bank',
                    'transaction_date' => $txDate,
                ]);
            } else {
                Transaction::create([
                    'user_id' => $request->user()->id,
                    'monthly_obligation_id' => $obligation->id,
                    'category_id' => $catId,
                    'type' => 'expense',
                    'amount' => $obligation->amount,
                    'description' => $txDescription,
                    'payment_method' => 'Bank',
                    'transaction_date' => $txDate,
                ]);
            }

            WealthPlannerService::clearUserCache($request->user()->id);

            $msg = $obligation->total_installments
                ? "'{$obligation->name}' (Cicilan ke-{$obligation->paid_installments}/{$obligation->total_installments}x) ditandai LUNAS dan dicatat ke pengeluaran."
                : "'{$obligation->name}' ditandai LUNAS dan dicatat ke pengeluaran.";
        } else {
            $payment->is_paid = false;
            $payment->paid_amount = 0.00;
            $payment->paid_at = null;
            $payment->save();

            // Decrement paid_installments jika sebelumnya bertambah
            if ($obligation->total_installments && $obligation->paid_installments > 0) {
                $obligation->decrement('paid_installments');
                $obligation->refresh();
            }

            // Remove corresponding transaction for this period
            Transaction::where('user_id', $request->user()->id)
                ->where('monthly_obligation_id', $obligation->id)
                ->whereMonth('transaction_date', $month)
                ->whereYear('transaction_date', $year)
                ->forceDelete();

            WealthPlannerService::clearUserCache($request->user()->id);

            $msg = "'{$obligation->name}' dikembalikan ke status BELUM BAYAR (Dana Terkunci) dan pengeluaran terkait dihapus.";
        }

        return back()->with('success', $msg);
    }
}
