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

    private function addEmpresaIdIfMissing(string $table, ?string $afterColumn = 'id'): void
    {
        if (!$this->tableExists($table)) {
            return;
        }

        if ($this->columnExists($table, 'empresa_id')) {
            return;
        }

        $afterSql = $afterColumn ? " AFTER `$afterColumn`" : "";

        DB::statement("
            ALTER TABLE `$table`
            ADD COLUMN `empresa_id` BIGINT UNSIGNED NULL{$afterSql}
        ");
    }

    private function makeEmpresaIdNotNull(string $table): void
    {
        if (!$this->tableExists($table) || !$this->columnExists($table, 'empresa_id')) {
            return;
        }

        DB::statement("
            ALTER TABLE `$table`
            MODIFY `empresa_id` BIGINT UNSIGNED NOT NULL
        ");
    }

    public function up(): void
    {
        $empresaDefaultId = DB::table('empresas')->min('id');

        if (!$empresaDefaultId) {
            throw new RuntimeException('No hay empresas en la tabla empresas. Crea al menos una empresa antes de correr esta migración.');
        }

        // 1) Agregar empresa_id como NULLABLE primero
        $this->addEmpresaIdIfMissing('servicios');
        $this->addEmpresaIdIfMissing('mantenimiento_reglas');
        $this->addEmpresaIdIfMissing('mantenimiento_realizados');
        $this->addEmpresaIdIfMissing('equipo_mediciones_eventos');

        // 2) Backfill desde equipos
        if (
            $this->tableExists('mantenimiento_reglas') &&
            $this->tableExists('equipos') &&
            $this->columnExists('mantenimiento_reglas', 'empresa_id') &&
            $this->columnExists('mantenimiento_reglas', 'equipo_id') &&
            $this->columnExists('equipos', 'empresa_id')
        ) {
            DB::statement("
                UPDATE `mantenimiento_reglas` mr
                JOIN `equipos` e ON e.id = mr.equipo_id
                SET mr.empresa_id = e.empresa_id
                WHERE mr.empresa_id IS NULL
                  AND e.empresa_id IS NOT NULL
            ");
        }

        if (
            $this->tableExists('mantenimiento_realizados') &&
            $this->tableExists('equipos') &&
            $this->columnExists('mantenimiento_realizados', 'empresa_id') &&
            $this->columnExists('mantenimiento_realizados', 'equipo_id') &&
            $this->columnExists('equipos', 'empresa_id')
        ) {
            DB::statement("
                UPDATE `mantenimiento_realizados` mra
                JOIN `equipos` e ON e.id = mra.equipo_id
                SET mra.empresa_id = e.empresa_id
                WHERE mra.empresa_id IS NULL
                  AND e.empresa_id IS NOT NULL
            ");
        }

        if (
            $this->tableExists('equipo_mediciones_eventos') &&
            $this->tableExists('equipos') &&
            $this->columnExists('equipo_mediciones_eventos', 'empresa_id') &&
            $this->columnExists('equipo_mediciones_eventos', 'equipo_id') &&
            $this->columnExists('equipos', 'empresa_id')
        ) {
            DB::statement("
                UPDATE `equipo_mediciones_eventos` eme
                JOIN `equipos` e ON e.id = eme.equipo_id
                SET eme.empresa_id = e.empresa_id
                WHERE eme.empresa_id IS NULL
                  AND e.empresa_id IS NOT NULL
            ");
        }

        // 3) Backfill servicios desde reglas
        if (
            $this->tableExists('servicios') &&
            $this->tableExists('mantenimiento_reglas') &&
            $this->columnExists('servicios', 'empresa_id') &&
            $this->columnExists('mantenimiento_reglas', 'servicio_id') &&
            $this->columnExists('mantenimiento_reglas', 'empresa_id')
        ) {
            DB::statement("
                UPDATE `servicios` s
                JOIN `mantenimiento_reglas` mr ON mr.servicio_id = s.id
                SET s.empresa_id = mr.empresa_id
                WHERE s.empresa_id IS NULL
                  AND mr.empresa_id IS NOT NULL
            ");
        }

        // 4) Backfill servicios desde realizados
        if (
            $this->tableExists('servicios') &&
            $this->tableExists('mantenimiento_realizados') &&
            $this->columnExists('servicios', 'empresa_id') &&
            $this->columnExists('mantenimiento_realizados', 'servicio_id') &&
            $this->columnExists('mantenimiento_realizados', 'empresa_id')
        ) {
            DB::statement("
                UPDATE `servicios` s
                JOIN `mantenimiento_realizados` mra ON mra.servicio_id = s.id
                SET s.empresa_id = mra.empresa_id
                WHERE s.empresa_id IS NULL
                  AND mra.empresa_id IS NOT NULL
            ");
        }

        // 5) Fallback final
        foreach ([
            'servicios',
            'mantenimiento_reglas',
            'mantenimiento_realizados',
            'equipo_mediciones_eventos',
        ] as $tableName) {
            if (!$this->tableExists($tableName) || !$this->columnExists($tableName, 'empresa_id')) {
                continue;
            }

            DB::statement("
                UPDATE `$tableName`
                SET `empresa_id` = {$empresaDefaultId}
                WHERE `empresa_id` IS NULL
                   OR `empresa_id` = 0
                   OR `empresa_id` = ''
            ");
        }

        // 6) Forzar NOT NULL con SQL directo
        $this->makeEmpresaIdNotNull('servicios');
        $this->makeEmpresaIdNotNull('mantenimiento_reglas');
        $this->makeEmpresaIdNotNull('mantenimiento_realizados');
        $this->makeEmpresaIdNotNull('equipo_mediciones_eventos');

        // 7) Agregar índices, sin FK por ahora para evitar broncas en producción
        foreach ([
            'servicios',
            'mantenimiento_reglas',
            'mantenimiento_realizados',
            'equipo_mediciones_eventos',
        ] as $tableName) {
            if (!$this->tableExists($tableName) || !$this->columnExists($tableName, 'empresa_id')) {
                continue;
            }

            $indexExists = DB::selectOne("
                SELECT COUNT(*) AS total
                FROM information_schema.STATISTICS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = ?
                  AND INDEX_NAME = ?
            ", [$tableName, $tableName . '_empresa_id_index']);

            if ((int) $indexExists->total === 0) {
                DB::statement("
                    ALTER TABLE `$tableName`
                    ADD INDEX `{$tableName}_empresa_id_index` (`empresa_id`)
                ");
            }
        }
    }

    public function down(): void
    {
        foreach ([
            'equipo_mediciones_eventos',
            'mantenimiento_realizados',
            'mantenimiento_reglas',
            'servicios',
        ] as $tableName) {
            if (!$this->tableExists($tableName) || !$this->columnExists($tableName, 'empresa_id')) {
                continue;
            }

            DB::statement("
                ALTER TABLE `$tableName`
                DROP COLUMN `empresa_id`
            ");
        }
    }
};