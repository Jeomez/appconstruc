<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateUserAdminSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')
            ->where('id', 1)
            ->update([
                'empresa' => 1,
                'rol'     => 1,
            ]);
    }
}