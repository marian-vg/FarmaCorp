<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Batch extends Model
{
    /** @use HasFactory<\Database\Factories\BatchFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'medicine_id',
        'batch_number',
        'expiration_date',
        'initial_quantity',
        'current_quantity',
        'minimum_stock'
    ];

    protected function casts(): array
    {
        return [
            'expiration_date' => 'date',
            'initial_quantity' => 'integer',
            'current_quantity' => 'integer',
            'minimum_stock' => 'integer'
        ];
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class, 'medicine_id', 'product_id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
}
