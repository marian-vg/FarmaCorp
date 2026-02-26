<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasPermissions;

class Profile extends Model
{
    use HasPermissions;

    protected $fillable = ['name', 'description'];

    protected $guard_name = 'web';

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
