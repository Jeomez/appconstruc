<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConsecutivoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('equipo_consecutivos')->insert([
            [
                'tipo' => 'CA',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tipo' => 'MA',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tipo' => 'EQ',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tipo' => 'VE',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
