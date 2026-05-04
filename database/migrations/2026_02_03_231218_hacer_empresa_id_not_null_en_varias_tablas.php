<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('obras', fn(Blueprint $t) => $t->foreignId('empresa_id')->nullable(false)->change());
        Schema::table('cargas', fn(Blueprint $t) => $t->foreignId('empresa_id')->nullable(false)->change());
        Schema::table('mantenimiento_realizados', fn(Blueprint $t) => $t->foreignId('empresa_id')->nullable(false)->change());
        Schema::table('equipo_mediciones_eventos', fn(Blueprint $t) => $t->foreignId('empresa_id')->nullable(false)->change());
        Schema::table('servicios', fn(Blueprint $t) => $t->foreignId('empresa_id')->nullable(false)->change());
        Schema::table('operadors', fn(Blueprint $t) => $t->foreignId('empresa_id')->nullable(false)->change());
        Schema::table('mantenimiento_reglas', fn(Blueprint $t) => $t->foreignId('empresa_id')->nullable(false)->change());
    }
};
