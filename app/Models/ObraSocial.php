<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ObraSocial extends Model
{
    protected $table = 'obras_sociales';
    protected $fillable = ['name', 'is_active'];

    // Vademécum: Medicamentos que cubre esta Obra Social
    public function medicines(): BelongsToMany
    {
        return $this->belongsToMany(Medicine::class, 'obra_social_medicine')
                    ->withPivot('discount_percentage')
                    ->withTimestamps();
    }

    // Clientes afiliados
    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 'client_obra_social')
                    ->withPivot('affiliate_number')
                    ->withTimestamps();
    }
}