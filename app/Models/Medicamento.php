<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Medicamento extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'producto_id';
    public $incrementing = false;

    protected $fillable = [
        'producto_id',
        'nivel',
        'prospecto',
        'fecha_vencimiento',
        'es_psicotropico',
        'grupo_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha_vencimiento' => 'date',
            'es_psicotropico' => 'boolean',
        ];
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
