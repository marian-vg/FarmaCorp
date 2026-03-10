<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Spatie\Permission\Traits\HasPermissions;

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
