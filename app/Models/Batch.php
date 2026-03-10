<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Batch extends Model
{
    /** @use HasFactory<\Database\Factories\BatchFactory> */
    use HasFactory, Searchable, SoftDeletes;

    protected $fillable = [
        'medicine_id',
        'batch_number',
        'expiration_date',
        'initial_quantity',
        'current_quantity',
        'minimum_stock',
    ];

    protected function casts(): array
    {
        return [
            'expiration_date' => 'date',
            'initial_quantity' => 'integer',
            'current_quantity' => 'integer',
            'minimum_stock' => 'integer',
        ];
    }

    /**
     * Scope a query to only include physically available and legally sellable batches.
     * (Quantity > 0 and expiration_date >= today)
     */
    public function scopeVendibles($query)
    {
        return $query->where('current_quantity', '>', 0)
            ->where('expiration_date', '>=', now()->toDateString());
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class, 'medicine_id', 'id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
}
