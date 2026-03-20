<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Prescription extends Model
{
    use HasFactory;

    protected $fillable = [
        'factura_id',
        'client_id',
        'file_path',
        'doctor_license',
        'prescription_date',
        'authorization_code'
    ];

    /**
     * Relación con la Factura (Venta)
     */
    public function factura(): BelongsTo
    {
        return $this->belongsTo(Factura::class);
    }

    /**
     * Relación con el Cliente
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}