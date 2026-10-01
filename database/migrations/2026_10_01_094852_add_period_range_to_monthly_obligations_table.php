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
            $table->unsignedTinyInteger('start_month')->nullable()->after('paid_installments');
            $table->unsignedSmallInteger('start_year')->nullable()->after('start_month');
            $table->unsignedTinyInteger('end_month')->nullable()->after('start_year');
            $table->unsignedSmallInteger('end_year')->nullable()->after('end_month');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monthly_obligations', function (Blueprint $table) {
            $table->dropColumn(['start_month', 'start_year', 'end_month', 'end_year']);
        });
    }
};
