<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedioPago extends Model
{
    protected $table = 'medio_pagos';

    protected $fillable = [
        'nombre',
        'tipo_medio',
        'recargo',
        'descuento',
    ];

    protected function casts(): array
    {
        return [
            'recargo' => 'decimal:2',
            'descuento' => 'decimal:2',
        ];
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoCaja::class, 'id_medio_pago');
    }
}
