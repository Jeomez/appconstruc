<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('roles')->truncate();

        DB::table('roles')->insert([
            [
                'id' => 1,
                'idstr' => 'ADM',
                'nombre' => 'Administrador',
                'activo' => 'Si',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'idstr' => 'DG',
                'nombre' => 'Director General',
                'activo' => 'Si',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'idstr' => 'DT',
                'nombre' => 'Director Técnico',
                'activo' => 'Si',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'idstr' => 'DA',
                'nombre' => 'Director Administrativo',
                'activo' => 'Si',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'idstr' => 'PC',
                'nombre' => 'Pendiente C',
                'activo' => 'Si',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 6,
                'idstr' => 'DP',
                'nombre' => 'DP',
                'activo' => 'Si',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 7,
                'idstr' => 'RO',
                'nombre' => 'Responsable de Obra',
                'activo' => 'Si',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 8,
                'idstr' => 'CTR',
                'nombre' => 'CTR',
                'activo' => 'Si',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 9,
                'idstr' => 'SI',
                'nombre' => 'SI',
                'activo' => 'Si',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}