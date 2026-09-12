<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Composite index for monthly & daily date-range filtering with type
            $table->index(['user_id', 'transaction_date', 'type'], 'tx_user_date_type_idx');
            $table->index(['user_id', 'type', 'transaction_date'], 'tx_user_type_date_idx');
            
            // Composite index for fast pagination and descending ledger sorting
            $table->index(['user_id', 'transaction_date', 'id'], 'tx_user_date_id_idx');
            
            // Index for obligation link lookups
            $table->index(['user_id', 'monthly_obligation_id'], 'tx_user_obligation_idx');
        });

        Schema::table('monthly_obligations', function (Blueprint $table) {
            // Composite index for active obligations ordered by due day
            $table->index(['user_id', 'is_active', 'due_day'], 'mo_user_active_due_idx');
        });

        Schema::table('categories', function (Blueprint $table) {
            // Index for default and user-owned categories lookup
            $table->index(['user_id', 'is_default'], 'cat_user_default_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex('cat_user_default_idx');
        });

        Schema::table('monthly_obligations', function (Blueprint $table) {
            $table->dropIndex('mo_user_active_due_idx');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('tx_user_date_type_idx');
            $table->dropIndex('tx_user_type_date_idx');
            $table->dropIndex('tx_user_date_id_idx');
            $table->dropIndex('tx_user_obligation_idx');
        });
    }
};
