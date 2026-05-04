<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('obras', function (Blueprint $table) {
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->restrictOnDelete();
        });

        Schema::table('cargas', function (Blueprint $table) {
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->restrictOnDelete();
        });

     
       

        // Si servicios/operadors/reglas van por empresa también:
        Schema::table('servicios', function (Blueprint $table) {
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->restrictOnDelete();
        });

        Schema::table('operadors', function (Blueprint $table) {
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->restrictOnDelete();
        });

        Schema::table('mantenimiento_reglas', function (Blueprint $table) {
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->restrictOnDelete();
        });
    }
};
