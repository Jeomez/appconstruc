<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Buscar FK real de empresa_id en equipos
        $fk = DB::selectOne("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'equipos'
              AND COLUMN_NAME = 'empresa_id'
              AND REFERENCED_TABLE_NAME IS NOT NULL
            LIMIT 1
        ");

        // 2) Quitar FK solo si existe
        if ($fk) {
            DB::statement("ALTER TABLE equipos DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
        }

        // 3) Si hay registros con empresa_id NULL, asignarlos a empresa 1
        DB::table('equipos')
            ->whereNull('empresa_id')
            ->update(['empresa_id' => 1]);

        // 4) Hacer empresa_id NOT NULL
        Schema::table('equipos', function (Blueprint $table) {
            $table->unsignedBigInteger('empresa_id')->nullable(false)->change();
        });

        // 5) Crear FK nueva
        Schema::table('equipos', function (Blueprint $table) {
            $table->foreign('empresa_id')
                ->references('id')
                ->on('empresas')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        // Buscar FK real actual
        $fk = DB::selectOne("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'equipos'
              AND COLUMN_NAME = 'empresa_id'
              AND REFERENCED_TABLE_NAME IS NOT NULL
            LIMIT 1
        ");

        if ($fk) {
            DB::statement("ALTER TABLE equipos DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
        }

        Schema::table('equipos', function (Blueprint $table) {
            $table->unsignedBigInteger('empresa_id')->nullable()->change();

            $table->foreign('empresa_id')
                ->references('id')
                ->on('empresas')
                ->nullOnDelete();
        });
    }
};