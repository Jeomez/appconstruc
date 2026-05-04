<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Equipo;
use App\Models\Concerns\BelongsToEmpresa;

class MantenimientoRealizado extends Model
{
    protected $table = 'mantenimiento_realizados';

    use BelongsToEmpresa;
    
    protected $fillable = [
        'empresa_id',
        'equipo_id',
        'servicio_id',
        'fecha',
        'km_actual',
        'horas_actual',
        'notas',
        'costo',
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