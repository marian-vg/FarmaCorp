<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasPermissions;
use Laravel\Scout\Searchable;

class Profile extends Model
{
    use HasPermissions, Searchable;

    protected $fillable = ['name', 'description'];

    protected $guard_name = 'web';

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function toSearchableArray()
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
        ];
    }
}
