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
        Schema::table('users', function (Blueprint $table) {
            $table->string('daily_budget_mode')->default('auto')->after('monthly_start_day');
            $table->decimal('manual_daily_budget', 14, 2)->nullable()->after('daily_budget_mode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['daily_budget_mode', 'manual_daily_budget']);
        });
    }
};
