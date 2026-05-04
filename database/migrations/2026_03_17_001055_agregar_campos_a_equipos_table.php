<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipos', function (Blueprint $table) {

            // Dimensiones
            $table->decimal('largo', 10, 2)->nullable();
            $table->decimal('alto', 10, 2)->nullable();
            $table->decimal('ancho', 10, 2)->nullable();

            // Factura
            $table->string('factura')->nullable();

            // Imágenes principales
            $table->string('imagen_tarjeta')->nullable();
            $table->string('imagen_poliza')->nullable();
            $table->string('imagen_factura')->nullable();

            // Imágenes secundarias
            $table->string('imagen1')->nullable();
            $table->string('imagen2')->nullable();
            $table->string('imagen3')->nullable();
            $table->string('imagen4')->nullable();

            // Otros campos
            $table->string('clave', 4)->nullable();
            $table->string('id_unico', 20)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('equipos', function (Blueprint $table) {

            $table->dropColumn([
                'largo',
                'alto',
                'ancho',
                'factura',
                'imagen_tarjeta',
                'imagen_poliza',
                'imagen_factura',
                'imagen1',
                'imagen2',
                'imagen3',
                'imagen4',
                'clave',
                'id_unico'
            ]);

        });
    }
};