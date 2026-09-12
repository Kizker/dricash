<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Update existing transactions to standardize payment methods
        // Bank
        DB::table('transactions')
            ->where(function ($query) {
                $query->where('payment_method', 'like', '%BCA%')
                    ->orWhere('payment_method', 'like', '%Mandiri%')
                    ->orWhere('payment_method', 'like', '%BNI%')
                    ->orWhere('payment_method', 'like', '%BRI%')
                    ->orWhere('payment_method', 'like', '%Bank%')
                    ->orWhere('payment_method', 'like', '%Transfer%');
            })
            ->update(['payment_method' => 'Bank']);

        // E-Wallet
        DB::table('transactions')
            ->where(function ($query) {
                $query->where('payment_method', 'like', '%QRIS%')
                    ->orWhere('payment_method', 'like', '%GoPay%')
                    ->orWhere('payment_method', 'like', '%OVO%')
                    ->orWhere('payment_method', 'like', '%Shopee%')
                    ->orWhere('payment_method', 'like', '%Dana%')
                    ->orWhere('payment_method', 'like', '%Wallet%');
            })
            ->update(['payment_method' => 'E-Wallet']);

        // Cash / Tunai
        DB::table('transactions')
            ->where(function ($query) {
                $query->where('payment_method', 'like', '%Cash%')
                    ->orWhere('payment_method', 'like', '%Tunai%');
            })
            ->update(['payment_method' => 'Cash / Tunai']);

        // Everything else that is not one of the four standard methods
        DB::table('transactions')
            ->whereNotIn('payment_method', ['Bank', 'E-Wallet', 'Cash / Tunai', 'Lainnya'])
            ->update(['payment_method' => 'Lainnya']);

        // 2. Set default value of payment_method column to 'Bank'
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('payment_method')->default('Bank')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('payment_method')->default('Transfer')->change();
        });
    }
};
