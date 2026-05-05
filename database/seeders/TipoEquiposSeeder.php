<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoEquiposSeeder extends Seeder
{
    public function run(): void
    {
        // ⚠️ Desactiva FK por si hay relaciones
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // 🧹 Limpia la tabla (doble underscore)
        DB::table('tipo__equipos')->truncate();

        // 🔁 Reactiva FK
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 🚀 Inserta los registros
        DB::table('tipo__equipos')->insert([
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
        ]);
    }
}