<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

class Client extends Model
{
    use Searchable;

    public $modalTab = 'info';

    public $selectedClientId = null;

    public $facturaSeleccionada = null;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function facturas(): HasMany
    {
        return $this->hasMany(Factura::class, 'cliente_id');
    }

    public function toSearchableArray()
    {
        return [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
        ];
    }
}
