<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmpresaSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('empresas')->updateOrInsert(
            ['id' => 1],
            [
                'nombre'   => 'SJS Construcciones',
                'direccion'=> 'Rendon #348',
                'cp'       => '81200',
                'rfc'      => 'XAXX010101000',
                'telefono' => '8888888888',
                'activo'   => 'Si',
            ]
        );

        $this->command->info('Empresa creada correctamente.');
    }
}