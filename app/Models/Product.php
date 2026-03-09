<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Scout\Searchable;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory, SoftDeletes, Searchable;

    protected $fillable = [
        'name',
        'description',
        'status',
        'price_updated_at',
        'price_expires_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'price_updated_at' => 'datetime',
            'price_expires_at' => 'date',
        ];
    }

    public function stock(): HasOne
    {
        return $this->hasOne(Stock::class, 'product_id');
    }

    public function medicine(): HasOne
    {
        return $this->hasOne(Medicine::class);
    }

    public function toSearchableArray()
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
        ];
    }
}
