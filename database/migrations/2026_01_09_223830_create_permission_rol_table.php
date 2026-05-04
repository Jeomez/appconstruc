<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('permission_rol', function (Blueprint $table) {
            $table->unsignedBigInteger('rol_id');
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();

            $table->enum('effect', ['allow', 'deny'])->default('deny');
            $table->enum('scope', ['all', 'assigned_equipment'])->nullable();
            // scope NULL cuando no aplica (ej. reportes.export)

            $table->timestamps();

            $table->primary(['rol_id', 'permission_id']);

            $table->foreign('rol_id')
                ->references('id')
                ->on('roles')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_rol');
    }
};

