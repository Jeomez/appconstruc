<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Equipo;
use App\Models\Concerns\BelongsToEmpresa;

class EquipoMedicionesEvento extends Model
{
    protected $table = 'equipo_mediciones_eventos';

    use BelongsToEmpresa;

    protected $fillable = [
        'empresa_id',
        'equipo_id',
        'km_antes',
        'km_despues',
        'horas_antes',
        'horas_despues',
        'motivo',
        'fecha',
    ];

    public function equipo()
    {
        return $this->belongsTo(Equipo::class);
    }
}
