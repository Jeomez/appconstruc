<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::table('equipos', function (Blueprint $table) {
            // Medidor 2
            $table->integer('rango_inferior2')->nullable();
            $table->integer('rango_superior2')->nullable();
            $table->decimal('unidad_rend2')->nullable();

            // Medidor 3
            $table->integer('rango_inferior3')->nullable();
            $table->integer('rango_superior3')->nullable();
            $table->decimal('unidad_rend3')->nullable();
          
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipos', function (Blueprint $table) {
            //
        });
    }
};
