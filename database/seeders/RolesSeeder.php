<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['id' => 1, 'idstr' => 'ADM', 'nombre' => 'Administrador', 'activo' => 'Si'],
            ['id' => 2, 'idstr' => 'DG',  'nombre' => 'Director General', 'activo' => 'Si'],
            ['id' => 3, 'idstr' => 'DT',  'nombre' => 'Director Técnico', 'activo' => 'Si'],
            ['id' => 4, 'idstr' => 'DA',  'nombre' => 'Director Administrativo', 'activo' => 'Si'],
            ['id' => 5, 'idstr' => 'PC',  'nombre' => 'Pendiente C', 'activo' => 'Si'],
            ['id' => 6, 'idstr' => 'DP',  'nombre' => 'DP', 'activo' => 'Si'],
            ['id' => 7, 'idstr' => 'RO',  'nombre' => 'Responsable de Obra', 'activo' => 'Si'],
            ['id' => 8, 'idstr' => 'CTR', 'nombre' => 'CTR', 'activo' => 'Si'],
            ['id' => 9, 'idstr' => 'SI',  'nombre' => 'SI', 'activo' => 'Si'],
        ];

        foreach ($roles as $rol) {
            DB::table('roles')->updateOrInsert(
                ['id' => $rol['id']],
                [
                    'idstr' => $rol['idstr'],
                    'nombre' => $rol['nombre'],
                    'activo' => $rol['activo'],
                    'updated_at' => now(),
                    'created_at' => DB::raw('COALESCE(created_at, NOW())'),
                ]
            );
        }
    }
}