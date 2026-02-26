<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovimientoCaja extends Model
{
    protected $fillable = [
        'tipo_movimiento',
        'monto',
        'fecha_movimiento',
        'id_medio_pago',
        'id_caja',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
            'fecha_movimiento' => 'datetime',
        ];
    }

    public function caja()
    {
        return $this->belongsTo(Caja::class, 'id_caja');
    }

    public function medioPago()
    {
        return $this->belongsTo(MedioPago::class, 'id_medio_pago');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
