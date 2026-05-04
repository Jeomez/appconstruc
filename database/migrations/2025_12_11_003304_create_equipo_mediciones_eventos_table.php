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
        Schema::create('equipo_mediciones_eventos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipo_id')->constrained('equipos');

            $table->integer('km_antes')->nullable();
            $table->integer('km_despues')->nullable();

            $table->integer('horas_antes')->nullable();
            $table->integer('horas_despues')->nullable();

            $table->string('motivo')->nullable();
            $table->dateTime('fecha');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipo_mediciones_eventos');
    }
};
