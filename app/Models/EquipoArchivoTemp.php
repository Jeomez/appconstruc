<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipoArchivoTemp extends Model
{
    protected $table = 'equipo_archivos_temp';

    protected $fillable = [
        'upload_token',
        'tipo',
        'nombre_original',
        'nombre_archivo',
        'ruta',
        'mime_type',
        'extension',
        'tamano',
        'es_imagen',
        'es_principal',
        'orden',
        'activo',
        'fecha_documento',
        'fecha_vencimiento',
        'observaciones',
    ];

    protected $casts = [
        'es_imagen' => 'boolean',
        'es_principal' => 'boolean',
        'activo' => 'boolean',
        'fecha_documento' => 'date',
        'fecha_vencimiento' => 'date',
    ];
}