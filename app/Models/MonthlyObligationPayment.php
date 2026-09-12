<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthlyObligationPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'monthly_obligation_id',
        'period_month',
        'period_year',
        'paid_amount',
        'paid_at',
        'is_paid',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'period_month' => 'integer',
            'period_year' => 'integer',
            'paid_amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'is_paid' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function monthlyObligation(): BelongsTo
    {
        return $this->belongsTo(MonthlyObligation::class);
    }
}
