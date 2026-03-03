<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacturaDetalle extends Model
{
    protected $fillable = [
        'cantidad',        // [cite: 526]
        'precio_unitario', // [cite: 527]
        'descuento',       // [cite: 527]
        'factura_id',      // FK [cite: 528]
        'product_id',      // FK [cite: 529]
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function factura(): BelongsTo
    {
        return $this->belongsTo(Factura::class);
    }
}