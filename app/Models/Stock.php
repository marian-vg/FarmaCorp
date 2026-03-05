<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stock extends Model
{
    // Definimos el nombre de la tabla si no sigue el estándar (opcional)
    protected $table = 'stocks';
    
    // Usamos product_id como llave primaria si así lo definieron (Pág. 18) [cite: 558]
    protected $primaryKey = 'product_id';
    public $incrementing = false;

    protected $fillable = [
        'product_id',       // [cite: 558]
        'stock_minimo',     // [cite: 561]
        'stock_máximo',     // [cite: 571]
        'cantidad_actual',  // [cite: 573]
        'fecha_actualización', // [cite: 572]
    ];

    protected $casts = [
        'fecha_actualización' => 'datetime',
        'cantidad_actual' => 'integer',
        'stock_minimo' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}