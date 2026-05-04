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
        Schema::create('mantenimiento_reglas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipo_id')->constrained('equipos');
            $table->foreignId('servicio_id')->constrained('servicios');

            $table->integer('cada_km')->nullable();      // ej 5000 km
            $table->integer('cada_horas')->nullable();   // ej 200 horas
            $table->integer('cada_dias')->nullable();    // ej 180 días

            $table->integer('kilometraje_inicial')->nullable();
            $table->integer('horas_inicial')->nullable();
            $table->date('fecha_inicio')->nullable();

            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mantenimiento_reglas');
    }
};
