<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
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
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'empresa_id')) {
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
        $tables = [
            'mantenimiento_reglas',
            'operadors',
            'servicios',
            'cargas',
            'obras',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'empresa_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropConstrainedForeignId('empresa_id');
                });
            }
        }
    }
};