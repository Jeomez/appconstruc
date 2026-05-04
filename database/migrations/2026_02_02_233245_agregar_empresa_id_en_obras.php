<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cargas', function (Blueprint $table) {
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('obras', function (Blueprint $table) {
            $table->dropConstrainedForeignId('empresa_id');
        });
    }
};
