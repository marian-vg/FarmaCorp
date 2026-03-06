<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Factura extends Model
{
    protected $fillable = [
    'tipo_comprobante',
    'fecha_emision',
    'total',
    'ajuste_global',
    'estado',
    'user_id',
    'cliente_id',
    'medio_pago_id',
];

    protected $casts = [
        'fecha_emision' => 'datetime',
        'total' => 'decimal:2',
    ];

    public function pagos()
    {
        // Una factura puede tener varios movimientos de caja (pagos parciales)
        return $this->hasMany(MovimientoCaja::class, 'factura_id');
    }

    // Relación con el Empleado (Responsable)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relación con los productos vendidos (Detalles)
    public function details(): HasMany
    {
        return $this->hasMany(FacturaDetalle::class);
    }
    
    // Relación opcional con el medio de pago (si lo agregas al modelo luego)
    public function medioPago(): BelongsTo
    {
        return $this->belongsTo(MedioPago::class, 'medio_pago_id');
    }
}