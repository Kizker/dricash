<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\GrowthTarget;
use App\Models\MonthlyObligation;
use App\Models\MonthlyObligationPayment;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Default Categories
        $categoriesData = [
            // Income
            ['name' => 'Gaji Pokok', 'type' => 'income', 'icon' => 'Wallet', 'color' => '#10B981', 'is_default' => true],
            ['name' => 'Freelance & Side Job', 'type' => 'income', 'icon' => 'Briefcase', 'color' => '#06B6D4', 'is_default' => true],
            ['name' => 'Bonus & Dividen', 'type' => 'income', 'icon' => 'TrendingUp', 'color' => '#8B5CF6', 'is_default' => true],
            ['name' => 'Cashback & Lainnya', 'type' => 'income', 'icon' => 'Gift', 'color' => '#EC4899', 'is_default' => true],
            
            // Obligations (Fixed Expenses)
            ['name' => 'Sewa Kos / Hunian', 'type' => 'obligation', 'icon' => 'Home', 'color' => '#F59E0B', 'is_default' => true],
            ['name' => 'Cicilan & Pinjaman', 'type' => 'obligation', 'icon' => 'CreditCard', 'color' => '#EF4444', 'is_default' => true],
            ['name' => 'Listrik & Utilitas', 'type' => 'obligation', 'icon' => 'Zap', 'color' => '#F97316', 'is_default' => true],
            ['name' => 'Internet & Komunikasi', 'type' => 'obligation', 'icon' => 'Wifi', 'color' => '#3B82F6', 'is_default' => true],
            ['name' => 'Asuransi & BPJS', 'type' => 'obligation', 'icon' => 'ShieldCheck', 'color' => '#14B8A6', 'is_default' => true],
            
            // Daily Expenses
            ['name' => 'Makanan & Minuman', 'type' => 'expense', 'icon' => 'Utensils', 'color' => '#F43F5E', 'is_default' => true],
            ['name' => 'Transportasi & Bensin', 'type' => 'expense', 'icon' => 'Car', 'color' => '#0EA5E9', 'is_default' => true],
            ['name' => 'Belanja Kebutuhan Pokok', 'type' => 'expense', 'icon' => 'ShoppingCart', 'color' => '#84CC16', 'is_default' => true],
            ['name' => 'Kopi & Nongkrong', 'type' => 'expense', 'icon' => 'Coffee', 'color' => '#D97706', 'is_default' => true],
            ['name' => 'Hiburan & Hobi', 'type' => 'expense', 'icon' => 'Film', 'color' => '#A855F7', 'is_default' => true],
            ['name' => 'Langganan Digital', 'type' => 'expense', 'icon' => 'Tv', 'color' => '#6366F1', 'is_default' => true],
            ['name' => 'Lain-lain', 'type' => 'expense', 'icon' => 'Tag', 'color' => '#64748B', 'is_default' => true],
        ];

        $categories = [];
        foreach ($categoriesData as $data) {
            $cat = Category::create($data);
            $categories[$data['name']] = $cat;
        }

        // 2. Create Super Admin User
        $admin = User::create([
            'name' => 'Super Admin Dricash',
            'email' => 'admin@dricash.app',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
            'currency' => 'IDR',
            'monthly_start_day' => 1,
            'initial_net_worth' => 100000000.00,
        ]);

        // 3. Create Demo User
        $user = User::create([
            'name' => 'Andricha Deamitra',
            'email' => 'demo@dricash.app',
            'password' => Hash::make('password'),
            'role' => 'user',
            'is_active' => true,
            'currency' => 'IDR',
            'monthly_start_day' => 1,
            'initial_net_worth' => 35000000.00, // Rp 35.000.000 starting net worth
        ]);

        $now = Carbon::now();
        $currentMonth = (int) $now->format('m');
        $currentYear = (int) $now->format('Y');

        // 3. Create Monthly Obligations (Ring-fencing)
        $obligationsData = [
            [
                'name' => 'Sewa Kos Eksekutif',
                'category_id' => $categories['Sewa Kos / Hunian']->id,
                'amount' => 2500000.00,
                'due_day' => 5,
                'is_paid' => true,
                'paid_date' => $now->copy()->day(5),
            ],
            [
                'name' => 'Tagihan Listrik & PAM',
                'category_id' => $categories['Listrik & Utilitas']->id,
                'amount' => 450000.00,
                'due_day' => 15,
                'is_paid' => true,
                'paid_date' => $now->copy()->day(8),
            ],
            [
                'name' => 'WiFi Indihome 100Mbps',
                'category_id' => $categories['Internet & Komunikasi']->id,
                'amount' => 385000.00,
                'due_day' => 20,
                'is_paid' => false,
                'paid_date' => null,
            ],
            [
                'name' => 'Asuransi Kesehatan Manulife',
                'category_id' => $categories['Asuransi & BPJS']->id,
                'amount' => 750000.00,
                'due_day' => 25,
                'is_paid' => false,
                'paid_date' => null,
            ],
            [
                'name' => 'Cicilan Gadget Workstation',
                'category_id' => $categories['Cicilan & Pinjaman']->id,
                'amount' => 1200000.00,
                'due_day' => 28,
                'is_paid' => false,
                'paid_date' => null,
            ],
        ];

        foreach ($obligationsData as $obData) {
            $obligation = MonthlyObligation::create([
                'user_id' => $user->id,
                'category_id' => $obData['category_id'],
                'name' => $obData['name'],
                'amount' => $obData['amount'],
                'due_day' => $obData['due_day'],
                'is_active' => true,
                'notes' => 'Kewajiban bulanan rutin',
            ]);

            // Create Payment tracking record
            MonthlyObligationPayment::create([
                'user_id' => $user->id,
                'monthly_obligation_id' => $obligation->id,
                'period_month' => $currentMonth,
                'period_year' => $currentYear,
                'paid_amount' => $obData['is_paid'] ? $obData['amount'] : 0.00,
                'paid_at' => $obData['paid_date'],
                'is_paid' => $obData['is_paid'],
            ]);

            // If paid, create corresponding transaction
            if ($obData['is_paid']) {
                Transaction::create([
                    'user_id' => $user->id,
                    'category_id' => $obData['category_id'],
                    'monthly_obligation_id' => $obligation->id,
                    'type' => 'expense',
                    'amount' => $obData['amount'],
                    'transaction_date' => $obData['paid_date']->format('Y-m-d'),
                    'description' => 'Bayar '.$obData['name'],
                    'payment_method' => 'Bank',
                ]);
            }
        }

        // 4. Create Growth Target (Saving Goals)
        GrowthTarget::create([
            'user_id' => $user->id,
            'period_month' => $currentMonth,
            'period_year' => $currentYear,
            'target_growth_percentage' => 5.00, // Target 5% increase
            'target_savings_amount' => 2500000.00, // Target tabungan Rp 2.500.000
            'starting_net_worth' => 35000000.00,
            'notes' => 'Target tabungan dan investasi portofolio kuartal 3',
        ]);

        // 5. Create Incomes for Current Month
        Transaction::create([
            'user_id' => $user->id,
            'category_id' => $categories['Gaji Pokok']->id,
            'type' => 'income',
            'amount' => 15000000.00,
            'transaction_date' => $now->copy()->startOfMonth()->format('Y-m-d'),
            'description' => 'Gaji Pokok Bulanan (Payroll)',
            'payment_method' => 'Bank',
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'category_id' => $categories['Freelance & Side Job']->id,
            'type' => 'income',
            'amount' => 3500000.00,
            'transaction_date' => $now->copy()->day(4)->format('Y-m-d'),
            'description' => 'Honor UI/UX Design Project Milestone 1',
            'payment_method' => 'Bank',
        ]);

        // 6. Create Daily Expenses from Day 1 to Today
        $dailyTransactions = [
            ['day' => 1, 'cat' => 'Makanan & Minuman', 'amount' => 85000.00, 'desc' => 'Makan Siang & Kopi Pagi'],
            ['day' => 2, 'cat' => 'Belanja Kebutuhan Pokok', 'amount' => 220000.00, 'desc' => 'Belanja Mingguan Supermarket'],
            ['day' => 3, 'cat' => 'Transportasi & Bensin', 'amount' => 100000.00, 'desc' => 'Isi Bensin Pertamax'],
            ['day' => 4, 'cat' => 'Kopi & Nongkrong', 'amount' => 65000.00, 'desc' => 'Meeting Kopi dengan Klien'],
            ['day' => 5, 'cat' => 'Makanan & Minuman', 'amount' => 95000.00, 'desc' => 'Makan Malam Seafood'],
            ['day' => 6, 'cat' => 'Langganan Digital', 'amount' => 186000.00, 'desc' => 'Netflix Premium & Spotify Family'],
            ['day' => 7, 'cat' => 'Makanan & Minuman', 'amount' => 120000.00, 'desc' => 'Weekend Brunch'],
            ['day' => 8, 'cat' => 'Transportasi & Bensin', 'amount' => 75000.00, 'desc' => 'Grab Car Dinas Luar'],
            ['day' => 9, 'cat' => 'Kopi & Nongkrong', 'amount' => 48000.00, 'desc' => 'Es Kopi Susu Sore'],
            ['day' => 10, 'cat' => 'Makanan & Minuman', 'amount' => 55000.00, 'desc' => 'Makan Siang Nasi Padang'],
        ];

        foreach ($dailyTransactions as $dt) {
            $txDay = min((int)$now->format('d'), $dt['day']);
            Transaction::create([
                'user_id' => $user->id,
                'category_id' => $categories[$dt['cat']]->id,
                'type' => 'expense',
                'amount' => $dt['amount'],
                'transaction_date' => $now->copy()->day($txDay)->format('Y-m-d'),
                'description' => $dt['desc'],
                'payment_method' => 'E-Wallet',
            ]);
        }
    }
}
