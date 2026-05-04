<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rol;
use App\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'ADM',
            'DG',
            'DT',
            'DA',
            'PC',
            'DP',
            'RO',
            'CTR',
            'SI'
        ];

        foreach ($roles as $idstr) {
            Rol::firstOrCreate(
                ['idstr' => $idstr],
                ['nombre' => $idstr, 'activo' => 'Si']
            );
        }

        $matrix = [
            'motiv.view' => [
                'ADM' => 'SI',
                'DG' => 'SI',
                'DT' => 'SI',
                'DA' => 'SI',
                'PC' => 'SI',
                'DP' => 'SI',
                'RO' => 'SI',
                'CTR' => 'LIMITADO',
                'SI' => 'SI'
            ],
            'catalog.manage' => [
                'ADM' => 'SI',
                'DG' => 'NO',
                'DT' => 'LIMITADO',
                'DA' => 'SI',
                'PC' => 'LIMITADO',
                'DP' => 'LIMITADO',
                'RO' => 'NO',
                'CTR' => 'NO',
                'SI' => 'SI'
            ],
            'equipo.create_update' => [
                'ADM' => 'SI',
                'DG' => 'NO',
                'DT' => 'SI',
                'DA' => 'LIMITADO',
                'PC' => 'LIMITADO',
                'DP' => 'LIMITADO',
                'RO' => 'NO',
                'CTR' => 'NO',
                'SI' => 'LIMITADO'
            ],
            // 👉 aquí sigues igual con TODAS las filas
        ];

        foreach ($matrix as $permissionKey => $rolesConfig) {
            $permission = Permission::where('key', $permissionKey)->first();

            foreach ($rolesConfig as $rolIdstr => $value) {
                $rol = Rol::where('idstr', $rolIdstr)->first();

                if ($value === 'NO') {
                    $rol->permissions()->syncWithoutDetaching([
                        $permission->id => [
                            'effect' => 'deny',
                            'scope'  => 'all',
                        ]
                    ]);
                }

                if ($value === 'SI') {
                    $rol->permissions()->syncWithoutDetaching([
                        $permission->id => [
                            'effect' => 'allow',
                            'scope'  => 'all',
                        ]
                    ]);
                }

                if ($value === 'LIMITADO') {
                    $rol->permissions()->syncWithoutDetaching([
                        $permission->id => [
                            'effect' => 'allow',
                            'scope'  => 'assigned_equipment',
                        ]
                    ]);
                }
            }
        }
    }
}
