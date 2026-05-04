<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('equipo_consecutivos', function (Blueprint $table) {
            $table->id();
            $table->string('tipo', 10)->unique(); // VE, MA, EQ, CA
            $table->unsignedInteger('ultimo')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipo_consecutivos');
    }
};
