<?php

namespace App\Http\Controllers;

use App\Models\EquipoArchivo;
use App\Models\EquipoArchivoTemp;
use App\Services\EquipoArchivoService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EquipoArchivoTemporalController extends Controller
{
    public function __construct(
        protected EquipoArchivoService $service
    ) {}

    /**
     * Listar archivos temporales de un token
     */
    public function index(string $uploadToken)
    {
        $archivos = $this->service->listarTemporales($uploadToken);

        $mapArchivo = function (EquipoArchivoTemp $archivo) {
            return [
                'id' => $archivo->id,
                'upload_token' => $archivo->upload_token,
                'tipo' => $archivo->tipo,
                'nombre_original' => $archivo->nombre_original,
                'nombre_archivo' => $archivo->nombre_archivo,
                'mime_type' => $archivo->mime_type,
                'extension' => $archivo->extension,
                'tamano' => $archivo->tamano,
                'es_imagen' => $archivo->es_imagen,
                'es_principal' => $archivo->es_principal,
                'orden' => $archivo->orden,
                'activo' => $archivo->activo,
                'fecha_documento' => optional($archivo->fecha_documento)?->format('Y-m-d'),
                'fecha_vencimiento' => optional($archivo->fecha_vencimiento)?->format('Y-m-d'),
                'observaciones' => $archivo->observaciones,
                'created_at' => optional($archivo->created_at)?->format('Y-m-d H:i:s'),
                'updated_at' => optional($archivo->updated_at)?->format('Y-m-d H:i:s'),
            ];
        };

        $imagenPrincipal = $archivos->first(function ($a) {
            return $a->tipo === EquipoArchivo::TIPO_IMAGEN_PRINCIPAL && $a->activo;
        });

        $imagenesSecundarias = $archivos
            ->filter(function ($a) {
                return $a->tipo === EquipoArchivo::TIPO_IMAGEN_SECUNDARIA && $a->activo;
            })
            ->sortBy('orden')
            ->values()
            ->map($mapArchivo);

        $documentos = [
            'tarjeta_circulacion' => $archivos
                ->filter(fn ($a) => $a->tipo === EquipoArchivo::TIPO_TARJETA && $a->activo)
                ->values()
                ->map($mapArchivo),

            'poliza_seguro' => $archivos
                ->filter(fn ($a) => $a->tipo === EquipoArchivo::TIPO_POLIZA && $a->activo)
                ->values()
                ->map($mapArchivo),

            'factura_propiedad' => $archivos
                ->filter(fn ($a) => $a->tipo === EquipoArchivo::TIPO_FACTURA && $a->activo)
                ->values()
                ->map($mapArchivo),

            'otros' => $archivos
                ->filter(fn ($a) => $a->tipo === EquipoArchivo::TIPO_OTRO && $a->activo)
                ->values()
                ->map($mapArchivo),
        ];

        $historial = $archivos
            ->filter(fn ($a) => !$a->activo)
            ->values()
            ->map($mapArchivo);

        return response()->json([
            'imagen_principal' => $imagenPrincipal ? $mapArchivo($imagenPrincipal) : null,
            'imagenes_secundarias' => $imagenesSecundarias,
            'documentos' => $documentos,
            'historial' => $historial,
        ]);
    }

    /**
     * Subir archivo temporal
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'upload_token' => 'required|uuid',
            'archivo' => 'required|file|max:10240|mimes:jpg,jpeg,png,webp,pdf',
            'tipo' => 'required|string|in:' . implode(',', EquipoArchivo::TIPOS_VALIDOS),
            'fecha_documento' => 'nullable|date',
            'fecha_vencimiento' => 'nullable|date',
            'observaciones' => 'nullable|string|max:1000',
            'orden' => 'nullable|integer|min:0',
        ], [
            'archivo.max' => 'El archivo no debe exceder 10 MB.',
        ]);

        try {
            $archivo = $this->service->guardarTemporal($request->file('archivo'), $data);

            return response()->json([
                'message' => 'Archivo temporal subido correctamente.',
                'data' => [
                    'id' => $archivo->id,
                    'upload_token' => $archivo->upload_token,
                    'tipo' => $archivo->tipo,
                    'nombre_original' => $archivo->nombre_original,
                    'nombre_archivo' => $archivo->nombre_archivo,
                    'mime_type' => $archivo->mime_type,
                    'extension' => $archivo->extension,
                    'tamano' => $archivo->tamano,
                    'es_imagen' => $archivo->es_imagen,
                    'es_principal' => $archivo->es_principal,
                    'orden' => $archivo->orden,
                    'activo' => $archivo->activo,
                    'fecha_documento' => optional($archivo->fecha_documento)?->format('Y-m-d'),
                    'fecha_vencimiento' => optional($archivo->fecha_vencimiento)?->format('Y-m-d'),
                    'observaciones' => $archivo->observaciones,
                ]
            ], Response::HTTP_CREATED);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Ocurrió un error al subir el archivo temporal.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Eliminar archivo temporal
     */
    public function destroy(int $id)
    {
        try {
            $this->service->eliminarTemporal($id);

            return response()->json([
                'message' => 'Archivo temporal eliminado correctamente.'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'No se pudo eliminar el archivo temporal.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}