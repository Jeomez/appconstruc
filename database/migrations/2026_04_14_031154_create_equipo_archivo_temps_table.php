<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipo_archivos_temp', function (Blueprint $table) {
            $table->id();
            $table->uuid('upload_token')->index();

            $table->string('tipo');
            $table->string('nombre_original');
            $table->string('nombre_archivo');
            $table->string('ruta');
            $table->string('mime_type', 150)->nullable();
            $table->string('extension', 20)->nullable();
            $table->unsignedBigInteger('tamano')->default(0);

            $table->boolean('es_imagen')->default(false);
            $table->boolean('es_principal')->default(false);
            $table->integer('orden')->default(0);
            $table->boolean('activo')->default(true);

            $table->date('fecha_documento')->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->text('observaciones')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipo_archivos_temp');
    }
};