<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $fillable = ['key', 'name', 'module', 'description'];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Rol::class,
            'permission_rol',
            'permission_id',
            'rol_id'
        )->withPivot(['effect', 'scope'])->withTimestamps();
    }
}
