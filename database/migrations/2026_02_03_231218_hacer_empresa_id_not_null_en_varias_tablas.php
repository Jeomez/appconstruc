<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private function tableExists(string $table): bool
    {
        $result = DB::selectOne("
            SELECT COUNT(*) AS total
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
        ", [$table]);

        return (int) $result->total > 0;
    }

    private function columnExists(string $table, string $column): bool
    {
        $result = DB::selectOne("
            SELECT COUNT(*) AS total
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?
        ", [$table, $column]);

        return (int) $result->total > 0;
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

            DB::statement("
                UPDATE `$tableName`
                SET empresa_id = 1
                WHERE empresa_id IS NULL
                   OR empresa_id = 0
                   OR empresa_id = ''
            ");

            DB::statement("
                ALTER TABLE `$tableName`
                MODIFY `empresa_id` BIGINT UNSIGNED NOT NULL
            ");
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

            DB::statement("
                ALTER TABLE `$tableName`
                MODIFY `empresa_id` BIGINT UNSIGNED NULL
            ");
        }
    }
};