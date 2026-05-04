<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Equipo;

class MantenimientoRegla extends Model
{
    protected $table = 'mantenimiento_reglas';

    protected $fillable = [
        'empresa_id',
        'equipo_id',
        'servicio_id',
        'cada_km',
        'cada_horas',
        'cada_dias',
        'kilometraje_inicial',
        'horas_inicial',
        'fecha_inicio',
        'activo',
    ];

    public function equipo()
    {
        return $this->belongsTo(Equipo::class);
    }

    public function servicio()
    {
        return $this->belongsTo(Servicio::class);
    }
}
