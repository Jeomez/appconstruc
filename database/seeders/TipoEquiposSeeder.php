<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoEquiposSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            [
                'id' => 1,
                'nombre' => 'VEHICULOS',
                'activo' => 'Si',
            ],
            [
                'id' => 2,
                'nombre' => 'CAMIONES',
                'activo' => 'Si',
            ],
            [
                'id' => 3,
                'nombre' => 'MAQUINARIA',
                'activo' => 'Si',
            ],
            [
                'id' => 4,
                'nombre' => 'EQUIPO MENOR',
                'activo' => 'Si',
            ],
        ];

        foreach ($tipos as $tipo) {
            DB::table('tipo__equipos')->updateOrInsert(
                ['id' => $tipo['id']],
                [
                    'nombre' => $tipo['nombre'],
                    'activo' => $tipo['activo'],
                    'updated_at' => now(),
                    'created_at' => DB::raw('COALESCE(created_at, NOW())'),
                ]
            );
        }
    }
}