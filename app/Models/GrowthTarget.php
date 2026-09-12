<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrowthTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'period_month',
        'period_year',
        'target_growth_percentage',
        'target_savings_amount',
        'starting_net_worth',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'period_month' => 'integer',
            'period_year' => 'integer',
            'target_growth_percentage' => 'decimal:2',
            'target_savings_amount' => 'decimal:2',
            'starting_net_worth' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
