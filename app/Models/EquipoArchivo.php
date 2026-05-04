<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipoArchivo extends Model
{
    protected $table = 'equipo_archivos';

    protected $fillable = [
        'equipo_id',
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

    public const TIPO_IMAGEN_PRINCIPAL  = 'imagen_principal';
    public const TIPO_IMAGEN_SECUNDARIA = 'imagen_secundaria';
    public const TIPO_TARJETA           = 'tarjeta_circulacion';
    public const TIPO_POLIZA            = 'poliza_seguro';
    public const TIPO_FACTURA           = 'factura_propiedad';
    public const TIPO_OTRO              = 'otro';

    public const TIPOS_VALIDOS = [
        self::TIPO_IMAGEN_PRINCIPAL,
        self::TIPO_IMAGEN_SECUNDARIA,
        self::TIPO_TARJETA,
        self::TIPO_POLIZA,
        self::TIPO_FACTURA,
        self::TIPO_OTRO,
    ];

    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class);
    }
}