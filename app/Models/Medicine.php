<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

class Medicine extends Model
{
    use HasFactory, SoftDeletes, Searchable;

    protected $primaryKey = 'product_id';
    public $incrementing = false;

    protected $fillable = [
        'product_id',
        'presentation_name',
        'price',
        'level',
        'leaflet',
        'expiration_date',
        'is_psychotropic',
        'group_id',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'expiration_date' => 'date',
            'is_psychotropic' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    public function toSearchableArray(): array
    {
        return [
            'level' => $this->level,
            'leaflet' => $this->leaflet,
            // Include related data to enable searching by product or group name via DB engine
            'products.name' => $this->product ? $this->product->name : '',
            'groups.name' => $this->group ? $this->group->name : '',
        ];
    }
}
