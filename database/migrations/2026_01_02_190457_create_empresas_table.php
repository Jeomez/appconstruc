<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('direccion')->nullable();
            $table->string('cp', 10)->nullable();
            $table->string('rfc', 20)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('activo')->default('Si');
            $table->timestamps();

            $table->index(['nombre']);
            $table->index(['rfc']);
            $table->index(['activo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};
