<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('equipo_user', function (Blueprint $table) {
            $table->unsignedBigInteger('equipo_id');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->boolean('active')->default(true);
            $table->timestamp('assigned_at')->nullable();
            $table->timestamps();

            $table->primary(['equipo_id', 'user_id']);

            $table->foreign('equipo_id')
                ->references('id')
                ->on('equipos')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipo_user');
    }
};


