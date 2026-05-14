<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
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

    public function up(): void
    {
        $tables = [
            'obras',
            'cargas',
            'servicios',
            'operadors',
            'mantenimiento_reglas',
        ];

        foreach ($tables as $tableName) {
            if ($this->tableExists($tableName) && !$this->columnExists($tableName, 'empresa_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->foreignId('empresa_id')
                        ->nullable()
                        ->constrained('empresas')
                        ->restrictOnDelete();
                });
            }
        }
    }

    public function down(): void
    {
        // Por seguridad en producción lo dejamos vacío.
        // Si necesitas rollback, lo hacemos controlado.
    }
};