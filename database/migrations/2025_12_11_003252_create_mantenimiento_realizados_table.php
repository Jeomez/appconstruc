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
        Schema::create('mantenimiento_realizados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipo_id')->constrained('equipos');
            $table->foreignId('servicio_id')->constrained('servicios');

            $table->date('fecha');
            $table->integer('km_actual')->nullable();
            $table->integer('horas_actual')->nullable();

            $table->text('notas')->nullable();
            $table->decimal('costo', 10, 2)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mantenimiento_realizados');
    }
};
