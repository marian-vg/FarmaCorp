<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Stock extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'medicine_id',
        'cantidad_actual',
        'stock_minimo',
        'fecha_actualizacion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_actualizacion' => 'datetime',
            'cantidad_actual' => 'integer',
            'stock_minimo' => 'integer',
        ];
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }
}
