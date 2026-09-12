<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'type', // 'income', 'expense', 'obligation'
        'icon',
        'color',
        'is_default',
    ];

    protected static function booted(): void
    {
        static::saved(function (Category $category) {
            \App\Services\WealthPlannerService::clearCategoryCache($category->user_id);
            if ($category->user_id) {
                \App\Services\WealthPlannerService::clearUserCache($category->user_id);
            }
        });

        static::deleted(function (Category $category) {
            \App\Services\WealthPlannerService::clearCategoryCache($category->user_id);
            if ($category->user_id) {
                \App\Services\WealthPlannerService::clearUserCache($category->user_id);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function monthlyObligations(): HasMany
    {
        return $this->hasMany(MonthlyObligation::class);
    }
}
