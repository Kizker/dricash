<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'category_id',
        'monthly_obligation_id',
        'type', // 'income', 'expense'
        'amount',
        'transaction_date',
        'description',
        'payment_method',
        'is_growth_overridden',
    ];

    protected static function booted(): void
    {
        static::saved(function (Transaction $transaction) {
            \App\Services\WealthPlannerService::clearUserCache($transaction->user_id);
        });

        static::deleted(function (Transaction $transaction) {
            \App\Services\WealthPlannerService::clearUserCache($transaction->user_id);
        });
    }

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'transaction_date' => 'date',
            'is_growth_overridden' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function monthlyObligation(): BelongsTo
    {
        return $this->belongsTo(MonthlyObligation::class);
    }

    public function scopeIncome($query)
    {
        return $query->where('type', 'income');
    }

    public function scopeExpense($query)
    {
        return $query->where('type', 'expense');
    }
}
