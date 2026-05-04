<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

use App\Models\Concerns\BelongsToEmpresa;


class Equipo extends Model
{

    use BelongsToEmpresa;

    protected $fillable = [
        'numeco',
        'nombre',
        'tipo',
        'placas',
        'vigenciaplacas',
        'poliza',
        'vigenciapoliza',
        'noserie',
        'ulthorometro',
        'combustible',
        'responsable',
        'operador',
        'estado',
        'foto',
        'obra',
        'rango_inferior',
        'rango_superior',
        'unidad_rend',
        'id_operador',
        'peso',
        'apodo',
        'largo',
        'ancho',
        'alto',
        'factura',
        'imagen_tarjeta',
        'imagen_poliza',
        'imagen_factura',
        'imagen1',
        'imagen2',
        'imagen3',
        'imagen4',
        'clave',
        'id_unico',
        'rango_inferior2',
        'rango_superior2',
        'unidad_rend2',
        'rango_inferior3',
        'rango_superior3',
        'unidad_rend3',
        'libras',
    ];

    public function mantenimientoReglas()
    {
        return $this->hasMany(MantenimientoRegla::class);
    }

    public function mantenimientosRealizados()
    {
        return $this->hasMany(MantenimientoRealizado::class);
    }

    public function medicionesEventos()
    {
        return $this->hasMany(EquipoMedicionesEvento::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'equipo_user')
            ->withPivot(['active', 'assigned_at'])
            ->withTimestamps();
    }

    public function usuariosAsignados()
    {
        return $this->belongsToMany(User::class, 'equipo_user')
            ->wherePivot('active', 1)
            ->withPivot(['active', 'assigned_at'])
            ->withTimestamps();
    }


    public function equiposAsignados()
    {
        return $this->belongsToMany(Equipo::class, 'equipo_user')
            ->wherePivot('active', 1)
            ->withPivot(['active', 'assigned_at', 'unassigned_at'])
            ->withTimestamps();
    }

    protected static function booted(): void
    {
        static::creating(function ($equipo) {
            if (empty($equipo->id_unico)) {
                $equipo->id_unico = (string) Str::uuid();
            }
        });
    }

    public function archivos()
    {
        return $this->hasMany(\App\Models\EquipoArchivo::class);
    }
}
