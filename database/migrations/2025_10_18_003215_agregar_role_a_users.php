<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Si prefieres sólo admin/user, puedes usar enum; aquí dejo string flexible.
            $table->string('role', 50)->nullable()->default('user')->after('email');
            $table->index('role');
        });

        // Rellena registros existentes que queden en null (por si tu DB ignora el default en existentes)
        DB::table('users')->whereNull('role')->update(['role' => 'user']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropColumn('role');
        });
    }
};
