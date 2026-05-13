<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LimpiaEquiposSeeder extends Seeder
{
    public function run(): void
    {
        // Desactivar restricciones FK temporalmente
        Schema::disableForeignKeyConstraints();

        // Vaciar tabla
        DB::table('equipos')->truncate();

        // Reactivar restricciones FK
        Schema::enableForeignKeyConstraints();

        $this->command->info('Tabla equipos vaciada correctamente.');
    }
}