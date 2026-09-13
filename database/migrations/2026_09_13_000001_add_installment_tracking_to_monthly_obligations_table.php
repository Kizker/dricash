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
        Schema::table('monthly_obligations', function (Blueprint $table) {
            $table->unsignedSmallInteger('total_installments')->nullable()->after('due_day');
            $table->unsignedSmallInteger('paid_installments')->default(0)->after('total_installments');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monthly_obligations', function (Blueprint $table) {
            $table->dropColumn(['total_installments', 'paid_installments']);
        });
    }
};
