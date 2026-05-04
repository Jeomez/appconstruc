<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToEmpresa;

class Servicio extends Model
{
    use BelongsToEmpresa;
    
    protected $fillable = [
        'nombre',
        'tipo',
        'descripcion',
        'activo',
    ];

    public function reglas()
    {
        return $this->hasMany(MantenimientoRegla::class);
    }

    public function mantenimientosRealizados()
    {
        return $this->hasMany(MantenimientoRealizado::class);
    }
}
