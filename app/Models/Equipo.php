<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Equipo extends Model
{
    protected $fillable = ['numeco', 'nombre', 'tipo', 'placas', 'vigenciaplacas', 'poliza', 'vigenciapoliza', 'noserie',
                            'ulthorometro', 'combustible', 'responsable', 'operador', 'estado', 'foto', 'obra'];
}
