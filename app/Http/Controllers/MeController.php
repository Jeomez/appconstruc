<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MeController extends Controller
{
    public function permissions(Request $request)
    {
        $user = $request->user();

        // Cargar permisos del rol
        $rolePerms = $user->rol?->permissions ?? collect();

        $result = [];

        foreach ($rolePerms as $p) {
            $result[$p->key] = [
                'allow' => $p->pivot->effect === 'allow',
                'scope' => $p->pivot->scope, // all | assigned_equipment
                'source' => 'role',
            ];
        }

        // Aplicar overrides encima (si tienes relación y columnas)
        // Si tu override guarda allowed boolean y permission_id:
        foreach ($user->permissionOverrides ?? [] as $ov) {
            $key = $ov->permission?->key;
            if (!$key) continue;

            $result[$key] = [
                'allow' => (bool) $ov->allowed,
                'scope' => $result[$key]['scope'] ?? null, // por ahora no cambia scope
                'source' => 'override',
            ];
        }

        return response()->json([
            'user_id' => $user->id,
            'rol' => $user->rol?->idstr,
            'permissions' => $result,
        ]);
    }
}
