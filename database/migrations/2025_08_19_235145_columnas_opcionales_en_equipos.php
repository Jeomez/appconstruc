<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('equipos', function (Blueprint $table) {
            $table->string('placas')->nullable()->change();
            $table->date('vigenciaplacas')->nullable()->change();
            $table->string('poliza')->nullable()->change();
            $table->date('vigenciapoliza')->nullable()->change();
            $table->string('noserie')->nullable()->change();
            $table->integer('ulthorometro')->nullable()->change();
            $table->integer('combustible')->nullable()->change();
            $table->string('responsable')->nullable()->change();
            $table->string('operador')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipos', function (Blueprint $table) {
            $table->string('placas')->nullable(false)->change();
            $table->date('vigenciaplacas')->nullable(false)->change();
            $table->string('poliza')->nullable(false)->change();
            $table->date('vigenciapoliza')->nullable(false)->change();
            $table->string('noserie')->nullable(false)->change();
            $table->integer('ulthorometro')->nullable(false)->change();
            $table->integer('combustible')->nullable(false)->change();
            $table->string('responsable')->nullable(false)->change();
            $table->string('operador')->nullable(false)->change();
        });
    }
};
