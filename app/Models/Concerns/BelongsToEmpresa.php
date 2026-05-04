<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait BelongsToEmpresa
{
    protected static function bootBelongsToEmpresa(): void
    {
        // Filtra automáticamente por empresa del usuario autenticado
        static::addGlobalScope('empresa', function (Builder $builder) {
            // En consola (seeders/migraciones/tinker) normalmente NO hay sesión
            if (app()->runningInConsole()) return;

            $user = Auth::user();
            if (!$user || empty($user->empresa_id)) return;

            $builder->where(
                $builder->getModel()->getTable() . '.empresa_id',
                $user->empresa_id
            );
        });

        // Al crear, asigna empresa_id automáticamente (NO confiar en el front)
        static::creating(function ($model) {
            $user = Auth::user();
            if (!$user || empty($user->empresa_id)) return;

            if (empty($model->empresa_id)) {
                $model->empresa_id = $user->empresa_id;
            }
        });
    }
}
