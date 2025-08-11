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
        Schema::create('equipos', function (Blueprint $table) {
            $table->id();
            $table->string('numeco');
            $table->string('nombre');
            $table->integer('tipo');
            $table->string('placas');
            $table->date('vigenciaplacas');
            $table->string('poliza');
            $table->date('vigenciapoliza');
            $table->string('noserie');
            $table->integer('ulthorometro');
            $table->integer('combustible');
            $table->string('responsable');
            $table->string('operador');
            $table->string('estado');
            $table->string('foto')->nullable();
            $table->integer('obra')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipos');
    }
};
