<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipo_archivos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('equipo_id')
                ->constrained('equipos')
                ->cascadeOnDelete();

            $table->string('tipo', 50); 
            // imagen_principal, imagen_secundaria, tarjeta_circulacion, poliza_seguro, factura_propiedad, otro

            $table->string('nombre_original');
            $table->string('nombre_archivo');
            $table->string('ruta');
            $table->string('mime_type', 100)->nullable();
            $table->string('extension', 20)->nullable();
            $table->unsignedBigInteger('tamano')->nullable();

            $table->boolean('es_imagen')->default(false);
            $table->boolean('es_principal')->default(false);
            $table->unsignedInteger('orden')->default(0);
            $table->boolean('activo')->default(true);

            $table->date('fecha_documento')->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->text('observaciones')->nullable();

            $table->timestamps();

            $table->index('equipo_id');
            $table->index('tipo');
            $table->index(['equipo_id', 'tipo']);
            $table->index(['equipo_id', 'activo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipo_archivos');
    }
};