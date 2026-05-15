<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    private function tableExists(string $table): bool
    {
        return Schema::hasTable($table);
    }

    private function columnExists(string $table, string $column): bool
    {
        return Schema::hasColumn($table, $column);
    }

    public function up(): void
    {
        $tables = [
            'obras',
            'cargas',
            'mantenimiento_realizados',
            'equipo_mediciones_eventos',
            'servicios',
            'operadors',
            'mantenimiento_reglas',
        ];

        foreach ($tables as $tableName) {
            if (!$this->tableExists($tableName) || !$this->columnExists($tableName, 'empresa_id')) {
                continue;
            }

            // Limpia registros existentes antes de hacer NOT NULL
            DB::table($tableName)
                ->whereNull('empresa_id')
                ->orWhere('empresa_id', 0)
                ->update(['empresa_id' => 1]);

            // Por si en producción quedó algún vacío extraño
            DB::statement("
                UPDATE {$tableName}
                SET empresa_id = 1
                WHERE empresa_id = ''
            ");

            Schema::table($tableName, function (Blueprint $table) {
                $table->unsignedBigInteger('empresa_id')->nullable(false)->change();
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'obras',
            'cargas',
            'mantenimiento_realizados',
            'equipo_mediciones_eventos',
            'servicios',
            'operadors',
            'mantenimiento_reglas',
        ];

        foreach ($tables as $tableName) {
            if (!$this->tableExists($tableName) || !$this->columnExists($tableName, 'empresa_id')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->unsignedBigInteger('empresa_id')->nullable()->change();
            });
        }
    }
};