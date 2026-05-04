<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['key' => 'motiv.view', 'name' => 'motiv.view', 'description' => 'Ver módulo MOTIV'],
            ['key' => 'catalog.manage', 'name' => 'catalog.manage', 'description' => 'Administrar catálogos y reglas'],
            ['key' => 'equipo.create_update', 'name' => 'equipo.create_update', 'description' => 'Alta / edición de equipos'],
            ['key' => 'equipo.deactivate', 'name' => 'equipo.deactivate', 'description' => 'Baja / inactivar equipos'],
            ['key' => 'carga.create', 'name' => 'carga.create', 'description' => 'Registrar carga de combustible'],
            ['key' => 'carga.update_cancel', 'name' => 'carga.update_cancel', 'description' => 'Editar / cancelar carga'],
            ['key' => 'carga.view_history', 'name' => 'carga.view_history', 'description' => 'Ver historial de cargas'],
            ['key' => 'rendimiento.view', 'name' => 'rendimiento.view', 'description' => 'Ver análisis de rendimiento'],
            ['key' => 'rendimiento.config', 'name' => 'rendimiento.config', 'description' => 'Configurar rendimientos esperados'],
            ['key' => 'carga.conciliar', 'name' => 'carga.conciliar', 'description' => 'Conciliar cargas vs proveedor'],
            ['key' => 'carga.autorizar', 'name' => 'carga.autorizar', 'description' => 'Autorizar conciliación'],
            ['key' => 'mantenimiento.create', 'name' => 'mantenimiento.create', 'description' => 'Registrar mantenimiento / servicio'],
            ['key' => 'falla.create', 'name' => 'falla.create', 'description' => 'Registrar falla / desperfecto'],
            ['key' => 'equipo.transfer', 'name' => 'equipo.transfer', 'description' => 'Registrar traslado de equipo'],
            ['key' => 'equipo.change_owner', 'name' => 'equipo.change_owner', 'description' => 'Cambiar responsable de equipo'],
            ['key' => 'reporte.operativo.view', 'name' => 'reporte.operativo.view', 'description' => 'Ver reportes operativos'],
            ['key' => 'reporte.ejecutivo.view', 'name' => 'reporte.ejecutivo.view', 'description' => 'Ver reporte ejecutivo'],
            ['key' => 'reporte.export', 'name' => 'reporte.export', 'description' => 'Exportar reportes'],
            ['key' => 'auditoria.view', 'name' => 'auditoria.view', 'description' => 'Ver auditoría'],
            ['key' => 'correccion.approve', 'name' => 'correccion.approve', 'description' => 'Aprobar correcciones'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['key' => $permission['key']],
                ['name' => $permission['name'], 'description' => $permission['description']]
            );
        }
    }
}
