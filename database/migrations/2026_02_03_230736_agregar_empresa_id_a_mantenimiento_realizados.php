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
         Schema::table('mantenimiento_realizados', function (Blueprint $table) {
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->restrictOnDelete();
        });
    }

    
};
