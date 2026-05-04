<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Permission;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


class Rol extends Model
{
    use HasFactory;

    protected $table = 'roles';

    protected $fillable = [
        'idstr',
        'nombre',
        'activo',
    ];

    public function users()
    {
        return $this->hasMany(\App\Models\User::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'permission_rol',
            'rol_id',
            'permission_id'
        )->withPivot(['effect', 'scope'])->withTimestamps();
    }
}
