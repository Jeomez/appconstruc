<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('idstr', 50)->unique(); // ejemplo: "ADMIN", "AGENTE", etc.
            $table->string('nombre');
            $table->string('activo')->default('Si');
            $table->timestamps();

            $table->index(['nombre']);
            $table->index(['activo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
