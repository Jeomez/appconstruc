<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\Permission;

class InstallController extends Controller
{
    /**
     * GET /api/install/status
     * Devuelve si el sistema necesita instalación o ya está instalado.
     */
    public function status()
    {
        $hasAnyUser = User::query()->exists();
        $hasAnyEmpresa = Empresa::query()->exists();

        return response()->json([
            'needs_install' => !($hasAnyUser || $hasAnyEmpresa),
            'has_users' => $hasAnyUser,
            'has_empresas' => $hasAnyEmpresa,
        ]);
    }

    /**
     * POST /api/install
     * Crea la 1ra empresa + rol ADMIN + usuario admin.
     * Solo se permite si NO existe ningún usuario.
     */
    public function install(Request $request)
    {
        if (User::query()->exists()) {
            return response()->json([
                'message' => 'El sistema ya está instalado (ya existen usuarios).'
            ], 409);
        }

        $data = $request->validate([
            // Empresa
            'empresa.nombre'    => ['required', 'string', 'max:255'],
            'empresa.direccion' => ['nullable', 'string', 'max:255'],
            'empresa.cp'        => ['nullable', 'string', 'max:10'],
            'empresa.rfc'       => ['nullable', 'string', 'max:20'],
            'empresa.telefono'  => ['nullable', 'string', 'max:20'],
            'empresa.activo'    => ['nullable', 'string', 'in:Si,No'],

            // Admin user
            'admin.name'     => ['required', 'string', 'max:255'],
            'admin.email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'admin.password' => ['required', 'string', 'min:8'],
        ]);

        $result = DB::transaction(function () use ($data) {
            // 1) Empresa
            $empresa = Empresa::create([
                'nombre'    => $data['empresa']['nombre'],
                'direccion' => $data['empresa']['direccion'] ?? null,
                'cp'        => $data['empresa']['cp'] ?? null,
                'rfc'       => $data['empresa']['rfc'] ?? null,
                'telefono'  => $data['empresa']['telefono'] ?? null,
                'activo'    => $data['empresa']['activo'] ?? 'Si',
            ]);

            // 2) Rol ADMIN (si no existe)
            $rolAdmin = Rol::firstOrCreate(
                ['idstr' => 'ADMIN'],
                ['nombre' => 'Administrador', 'activo' => 'Si']
            );

            // dar TODOS los permisos al admin
            $sync = Permission::query()->get()->mapWithKeys(function ($p) {
                return [$p->id => ['effect' => 'allow', 'scope' => '*']];
            })->toArray();

            $rolAdmin->permissions()->sync($sync);

            

            // 3) Usuario Admin
            $user = User::create([
                'name' => $data['admin']['name'],
                'email' => $data['admin']['email'],
                'password' => Hash::make($data['admin']['password']),
                'empresa_id' => $empresa->id,
                'rol_id' => $rolAdmin->id,
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;


            // Si usas Sanctum y quieres loguearlo de una vez:
            // $token = $user->createToken('install')->plainTextToken;

            return [
                'empresa' => $empresa,
                'rol_admin' => $rolAdmin,
                'user' => $user,
                'token' => $token,
            ];
        });

        return response()->json([
            'message' => 'Instalación completada.',
            'data' => $result,
        ], 201);
    }
}
