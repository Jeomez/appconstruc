<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RolPermissionController extends Controller
{
    // GET /roles/{rol}/permissions
    public function index(Rol $rol)
    {
        $all = Permission::query()
            ->orderBy('module')
            ->orderBy('name')
            ->get(['id','key','name','module','description']);

        $pivot = DB::table('permission_rol')
            ->where('rol_id', $rol->id)
            ->get()
            ->keyBy('permission_id');

        $permissions = $all->map(function ($p) use ($pivot) {
            $pv = $pivot->get($p->id);
            return [
                'id' => $p->id,
                'key' => $p->key,
                'name' => $p->name,
                'module' => $p->module,
                'description' => $p->description,
                'pivot' => $pv ? [
                    'effect' => $pv->effect,
                    'scope'  => $pv->scope,
                ] : null,
            ];
        });

        return response()->json([
            'rol' => $rol,
            'permissions' => $permissions,
        ]);
    }

    // PUT /roles/{rol}/permissions
    public function update(Request $request, Rol $rol)
    {
        $data = $request->validate([
            'items' => ['required','array'],
            'items.*.permission_id' => ['required','integer','exists:permissions,id'],
            'items.*.effect' => ['required','in:allow,deny'],
            'items.*.scope' => ['required','string','max:50'],
        ]);

        $sync = [];
        foreach ($data['items'] as $it) {
            $sync[$it['permission_id']] = [
                'effect' => $it['effect'],
                'scope'  => $it['scope'],
            ];
        }

        $rol->permissions()->sync($sync);

        return response()->json(['message' => 'Permisos actualizados']);
    }
}
