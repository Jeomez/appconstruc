<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Carga extends Model
{
    protected $table = 'cargas';
    protected $primaryKey = 'id_documento';
    public $incrementing = true;

    protected $fillable = [
        'serie','folio','fecha','hora','id_equipo','desc_equipo','horometro',
        'id_combustible','litros','id_obra','foto_ticket','foto_horometro',
        'latitud','longitud','mes', 'numeco'
    ];

    protected $casts = [
        'fecha' => 'date:Y-m-d',
        'hora'  => 'datetime:H:i:s',
        'litros'=> 'decimal:3',
        'latitud' => 'decimal:7',
        'longitud'=> 'decimal:7',
    ];

    // Siempre garantizar que "mes" = YYYYMM derivado de fecha
    public function setFechaAttribute($value)
    {
        $this->attributes['fecha'] = $value;
        $this->attributes['mes']   = \Carbon\Carbon::parse($value)->format('Ym');
    }

    // Scopes de filtrado
    public function scopeMes(Builder $q, ?string $mes): Builder
    {
        return $mes ? $q->where('mes', $mes) : $q;
    }

    public function scopeEquipo(Builder $q, $id_equipo): Builder
    {
        return $id_equipo ? $q->where('id_equipo', $id_equipo) : $q;
    }

    public function scopeObra(Builder $q, $id_obra): Builder
    {
        return $id_obra ? $q->where('id_obra', $id_obra) : $q;
    }
}
