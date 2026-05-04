<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipos', function (Blueprint $table) {
            // 1) quitar FK actual (SET NULL)
            $table->dropForeign(['empresa_id']);
        });

        Schema::table('equipos', function (Blueprint $table) {
            // 2) hacerla NOT NULL
            $table->unsignedBigInteger('empresa_id')->nullable(false)->change();
        });

        Schema::table('equipos', function (Blueprint $table) {
            // 3) volver a crear FK pero RESTRICT (o CASCADE)
            $table->foreign('empresa_id')
                ->references('id')->on('empresas')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('equipos', function (Blueprint $table) {
                
        });
    }
};
