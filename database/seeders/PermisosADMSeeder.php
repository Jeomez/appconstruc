<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PermisosADMSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $rol = DB::table('roles')->where('idstr', 'ADM')->first();

        if (!$rol) {
            $this->command->error('❌ Rol ADM no encontrado');
            return;
        }

        $permissions = DB::table('permissions')->get(['id']);

        if ($permissions->isEmpty()) {
            $this->command->error('❌ No hay permisos en la tabla permissions');
            return;
        }

        foreach ($permissions as $permission) {
            DB::table('permission_rol')->updateOrInsert(
                [
                    'rol_id' => $rol->id,
                    'permission_id' => $permission->id,
                ],
                [
                    'effect' => 'allow',
                    'scope' => 'all',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        $this->command->info("✅ ADM (rol_id={$rol->id}) tiene {$permissions->count()} permisos allow/all");
    }
}
