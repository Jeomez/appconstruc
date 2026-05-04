<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 0) Asegurar que exista al menos una empresa para poder backfillear servicios huérfanos
        $empresaDefaultId = DB::table('empresas')->min('id');
        if (!$empresaDefaultId) {
            throw new RuntimeException('No hay empresas en la tabla empresas. Crea al menos una empresa antes de correr esta migración.');
        }

        // 1) Agregar empresa_id como NULLABLE primero (para no romper con datos existentes)
        Schema::table('servicios', function (Blueprint $table) {
            if (!Schema::hasColumn('servicios', 'empresa_id')) {
                $table->foreignId('empresa_id')->nullable()->after('id')->constrained('empresas');
            }
        });

        Schema::table('mantenimiento_reglas', function (Blueprint $table) {
            if (!Schema::hasColumn('mantenimiento_reglas', 'empresa_id')) {
                $table->foreignId('empresa_id')->nullable()->after('id')->constrained('empresas');
            }
        });

        Schema::table('mantenimiento_realizados', function (Blueprint $table) {
            if (!Schema::hasColumn('mantenimiento_realizados', 'empresa_id')) {
                $table->foreignId('empresa_id')->nullable()->after('id')->constrained('empresas');
            }
        });

        Schema::table('equipo_mediciones_eventos', function (Blueprint $table) {
            if (!Schema::hasColumn('equipo_mediciones_eventos', 'empresa_id')) {
                $table->foreignId('empresa_id')->nullable()->after('id')->constrained('empresas');
            }
        });

        // 2) Backfill: reglas/realizados/eventos -> empresa_id por equipos.empresa_id (100% correcto)
        DB::statement("
            UPDATE mantenimiento_reglas mr
            JOIN equipos e ON e.id = mr.equipo_id
            SET mr.empresa_id = e.empresa_id
            WHERE mr.empresa_id IS NULL
        ");

        DB::statement("
            UPDATE mantenimiento_realizados mra
            JOIN equipos e ON e.id = mra.equipo_id
            SET mra.empresa_id = e.empresa_id
            WHERE mra.empresa_id IS NULL
        ");

        DB::statement("
            UPDATE equipo_mediciones_eventos eme
            JOIN equipos e ON e.id = eme.equipo_id
            SET eme.empresa_id = e.empresa_id
            WHERE eme.empresa_id IS NULL
        ");

        // 3) Backfill: servicios -> empresa_id desde reglas o realizados
        // 3a) Por reglas
        DB::statement("
            UPDATE servicios s
            JOIN mantenimiento_reglas mr ON mr.servicio_id = s.id
            SET s.empresa_id = mr.empresa_id
            WHERE s.empresa_id IS NULL
              AND mr.empresa_id IS NOT NULL
        ");

        // 3b) Por realizados (por si no tiene regla)
        DB::statement("
            UPDATE servicios s
            JOIN mantenimiento_realizados mra ON mra.servicio_id = s.id
            SET s.empresa_id = mra.empresa_id
            WHERE s.empresa_id IS NULL
              AND mra.empresa_id IS NOT NULL
        ");

        // 4) Fallback FINAL: servicios que siguen NULL (servicios “catálogo” aún no usados)
        // Se asignan a empresaDefaultId para poder forzar NOT NULL sin romper.
        DB::table('servicios')
            ->whereNull('empresa_id')
            ->update(['empresa_id' => $empresaDefaultId]);

        // 5) En este punto ya no deben existir nulls; forzamos NOT NULL
        // (requiere doctrine/dbal para change() en muchos setups)
        Schema::table('servicios', function (Blueprint $table) {
            $table->foreignId('empresa_id')->nullable(false)->change();
        });

        Schema::table('mantenimiento_reglas', function (Blueprint $table) {
            $table->foreignId('empresa_id')->nullable(false)->change();
        });

        Schema::table('mantenimiento_realizados', function (Blueprint $table) {
            $table->foreignId('empresa_id')->nullable(false)->change();
        });

        Schema::table('equipo_mediciones_eventos', function (Blueprint $table) {
            $table->foreignId('empresa_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        // Quitar FK y columna en orden
        Schema::table('equipo_mediciones_eventos', function (Blueprint $table) {
            if (Schema::hasColumn('equipo_mediciones_eventos', 'empresa_id')) {
                $table->dropConstrainedForeignId('empresa_id');
            }
        });

        Schema::table('mantenimiento_realizados', function (Blueprint $table) {
            if (Schema::hasColumn('mantenimiento_realizados', 'empresa_id')) {
                $table->dropConstrainedForeignId('empresa_id');
            }
        });

        Schema::table('mantenimiento_reglas', function (Blueprint $table) {
            if (Schema::hasColumn('mantenimiento_reglas', 'empresa_id')) {
                $table->dropConstrainedForeignId('empresa_id');
            }
        });

        Schema::table('servicios', function (Blueprint $table) {
            if (Schema::hasColumn('servicios', 'empresa_id')) {
                $table->dropConstrainedForeignId('empresa_id');
            }
        });
    }
};

