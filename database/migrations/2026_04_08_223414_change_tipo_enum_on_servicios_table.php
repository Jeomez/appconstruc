<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Permitir temporalmente valores viejos y nuevos
        DB::statement("
            ALTER TABLE servicios
            MODIFY COLUMN tipo ENUM('servicio', 'reparacion', 'preventivo', 'correctivo') NOT NULL
        ");

        // 2) Convertir datos existentes
        DB::table('servicios')
            ->where('tipo', 'servicio')
            ->update(['tipo' => 'preventivo']);

        DB::table('servicios')
            ->where('tipo', 'reparacion')
            ->update(['tipo' => 'correctivo']);

        // 3) Dejar el enum final solo con los nuevos valores
        DB::statement("
            ALTER TABLE servicios
            MODIFY COLUMN tipo ENUM('preventivo', 'correctivo') NOT NULL
        ");
    }

    public function down(): void
    {
        // 1) Permitir temporalmente valores nuevos y viejos
        DB::statement("
            ALTER TABLE servicios
            MODIFY COLUMN tipo ENUM('servicio', 'reparacion', 'preventivo', 'correctivo') NOT NULL
        ");

        // 2) Revertir datos
        DB::table('servicios')
            ->where('tipo', 'preventivo')
            ->update(['tipo' => 'servicio']);

        DB::table('servicios')
            ->where('tipo', 'correctivo')
            ->update(['tipo' => 'reparacion']);

        // 3) Restaurar enum original
        DB::statement("
            ALTER TABLE servicios
            MODIFY COLUMN tipo ENUM('servicio', 'reparacion') NOT NULL
        ");
    }
};