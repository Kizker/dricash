<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MonthlyObligation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'category_id',
        'name',
        'amount',
        'due_day',
        'total_installments',
        'paid_installments',
        'is_active',
        'notes',
    ];

    protected static function booted(): void
    {
        static::saved(function (MonthlyObligation $obligation) {
            \App\Services\WealthPlannerService::clearUserCache($obligation->user_id);
        });

        static::deleted(function (MonthlyObligation $obligation) {
            \App\Services\WealthPlannerService::clearUserCache($obligation->user_id);
        });
    }

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'due_day' => 'integer',
            'total_installments' => 'integer',
            'paid_installments' => 'integer',
            'is_active' => 'boolean',
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

    public function payments(): HasMany
    {
        return $this->hasMany(MonthlyObligationPayment::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Scope for active obligations
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
