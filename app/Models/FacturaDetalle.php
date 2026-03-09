<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacturaDetalle extends Model
{
    protected $fillable = [
        'cantidad',        
        'precio_unitario', 
        'descuento',       
        'factura_id',      
        'product_id',      
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