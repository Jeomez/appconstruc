<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Rol;
use App\Models\Permission;
use App\Models\UserPermissionOverride;


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;


    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'empresa_id',
        'rol_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function empresa()
    {
        return $this->belongsTo(\App\Models\Empresa::class);
    }

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }



    public function equipos()
    {
        return $this->belongsToMany(Equipo::class, 'equipo_user')
            ->withPivot(['active', 'assigned_at'])
            ->withTimestamps();
    }

    public function equiposAsignados()
    {
        return $this->belongsToMany(Equipo::class, 'equipo_user')
            ->wherePivot('active', 1)
            ->withPivot(['active', 'assigned_at'])
            ->withTimestamps();
    }

    public function permissionOverrides()
    {
        return $this->hasMany(UserPermissionOverride::class);
    }

    public function hasPermission(string $key): bool
    {
        // 1️⃣ Override del usuario
        $override = $this->permissionOverrides()
            ->whereHas('permission', fn($q) => $q->where('key', $key))
            ->first();

        if ($override) {
            return $override->allowed;
        }

        // 2️⃣ Permiso por rol
        $permission = $this->rol?->permissions()
            ->where('key', $key)
            ->first();

        if (!$permission) {
            return false;
        }

        return $permission->pivot->effect === 'allow';
    }

    public function getPermissionScope(string $key): ?string
    {
        // Overrides no manejan scope (por ahora)
        $permission = $this->rol?->permissions()
            ->where('key', $key)
            ->first();

        return $permission?->pivot?->scope;
    }
}
