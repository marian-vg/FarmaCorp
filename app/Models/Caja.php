<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Caja extends Model
{
    protected $fillable = [
        'fecha_apertura',
        'fecha_cierre',
        'monto_inicial',
        'monto_final',
        'user_id',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_apertura' => 'datetime',
            'fecha_cierre' => 'datetime',
            'monto_inicial' => 'decimal:2',
            'monto_final' => 'decimal:2',
        ];
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoCaja::class, 'id_caja');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
