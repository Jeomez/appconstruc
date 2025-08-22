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
        Schema::create('cargas', function (Blueprint $table) {
            $table->id('id_documento');

            // Identidad visible al usuario
            $table->string('serie', 10);
            $table->unsignedBigInteger('folio');

            // Datos de captura
            $table->date('fecha');                   // ej. 2025-08-12
            $table->string('mes', 6)->index();       // ej. "202508" (lo pides string)
            $table->time('hora')->nullable();        // hora de carga (HH:MM:SS)

            // Referencias/atributos
            $table->unsignedBigInteger('id_equipo')->index();
            $table->string('desc_equipo');
            $table->unsignedInteger('horometro');    // contador/lectura
            $table->unsignedBigInteger('id_combustible');
            $table->decimal('litros', 10, 3);        // precisión 0.001 L
            $table->unsignedBigInteger('id_obra')->index();

            // Evidencias
            $table->string('foto_ticket')->nullable();
            $table->string('foto_horometro')->nullable();

            // Geolocalización al capturar
            $table->decimal('latitud', 10, 7)->nullable();   // aprox ~1.1 cm
            $table->decimal('longitud', 10, 7)->nullable();

            
           

            // Integridad y performance
            $table->unique(['serie', 'folio']);      // no repetir folio dentro de una serie
            $table->index(['fecha', 'id_equipo']);   // búsquedas comunes

            $table->timestamps();                    // created_at / updated_at
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cargas');
    }
};
