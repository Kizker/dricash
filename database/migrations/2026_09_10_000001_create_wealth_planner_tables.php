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
        // 1. Categories Table
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('type'); // 'income', 'expense', 'obligation'
            $table->string('icon')->default('tag');
            $table->string('color')->default('#10B981');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['user_id', 'type']);
        });

        // 2. Monthly Obligations Table (Fixed Expenses & Ring-Fencing)
        Schema::create('monthly_obligations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->decimal('amount', 15, 2);
            $table->unsignedTinyInteger('due_day')->default(1);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'is_active']);
        });

        // 3. Monthly Obligation Payment Checklist
        Schema::create('monthly_obligation_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('monthly_obligation_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('period_month');
            $table->unsignedSmallInteger('period_year');
            $table->decimal('paid_amount', 15, 2)->default(0.00);
            $table->timestamp('paid_at')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['monthly_obligation_id', 'period_month', 'period_year'], 'unique_obligation_period_payment');
            $table->index(['user_id', 'period_month', 'period_year']);
        });

        // 4. Growth Targets (Net Worth Growth Target & Saving Goals)
        Schema::create('growth_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('period_month');
            $table->unsignedSmallInteger('period_year');
            $table->decimal('target_growth_percentage', 5, 2)->default(5.00);
            $table->decimal('target_savings_amount', 15, 2)->default(0.00);
            $table->decimal('starting_net_worth', 15, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'period_month', 'period_year'], 'unique_user_period_growth_target');
        });

        // 5. Transactions Table (Core Financial Ledger)
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('monthly_obligation_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type'); // 'income', 'expense'
            $table->decimal('amount', 15, 2);
            $table->date('transaction_date');
            $table->string('description');
            $table->string('payment_method')->default('Transfer');
            $table->boolean('is_growth_overridden')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'transaction_date']);
            $table->index(['user_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('growth_targets');
        Schema::dropIfExists('monthly_obligation_payments');
        Schema::dropIfExists('monthly_obligations');
        Schema::dropIfExists('categories');
    }
};
