<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Equipo;
use App\Models\EquipoArchivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EquipoArchivoController extends Controller
{
    /**
     * Listar archivos agrupados para el frontend
     */
    public function index(Equipo $equipo)
    {
        $archivos = $equipo->archivos()
            ->orderBy('orden')
            ->orderByDesc('id')
            ->get();

        $mapArchivo = function (EquipoArchivo $archivo) use ($equipo) {
            return [
                'id' => $archivo->id,
                'equipo_id' => $archivo->equipo_id,
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
                'url_ver' => route('equipos.archivos.ver', [
                    'equipo' => $equipo->id,
                    'archivo' => $archivo->id,
                ]),
                'url_descargar' => route('equipos.archivos.descargar', [
                    'equipo' => $equipo->id,
                    'archivo' => $archivo->id,
                ]),
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
     * Subir archivo
     */
    public function store(Request $request, Equipo $equipo)
    {
        $request->validate([
            'archivo' => 'required|file|max:10240|mimes:jpg,jpeg,png,webp,pdf',
            'tipo' => 'required|string|in:' . implode(',', EquipoArchivo::TIPOS_VALIDOS),
            'fecha_documento' => 'nullable|date',
            'fecha_vencimiento' => 'nullable|date',
            'observaciones' => 'nullable|string|max:1000',
            'orden' => 'nullable|integer|min:0',
        ], [
            'archivo.max' => 'El archivo no debe exceder 10 MB.',
        ]);

        $file = $request->file('archivo');
        $tipo = (string) $request->input('tipo');

        $mimeType = $file->getMimeType();
        $extension = strtolower($file->getClientOriginalExtension());
        $esImagen = Str::startsWith((string) $mimeType, 'image/');
        $esPrincipal = $tipo === EquipoArchivo::TIPO_IMAGEN_PRINCIPAL;

        if ($esPrincipal && !$esImagen) {
            return response()->json([
                'message' => 'La imagen principal debe ser un archivo de imagen.'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (
            in_array($tipo, [
                EquipoArchivo::TIPO_TARJETA,
                EquipoArchivo::TIPO_POLIZA,
                EquipoArchivo::TIPO_FACTURA,
            ], true)
            && !$esImagen
            && $mimeType !== 'application/pdf'
        ) {
            return response()->json([
                'message' => 'Ese tipo de documento solo permite PDF o imágenes.'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        DB::beginTransaction();

        try {
            // Solo una imagen principal activa
            if ($tipo === EquipoArchivo::TIPO_IMAGEN_PRINCIPAL) {
                $equipo->archivos()
                    ->where('tipo', EquipoArchivo::TIPO_IMAGEN_PRINCIPAL)
                    ->where('activo', true)
                    ->update([
                        'es_principal' => false,
                        'activo' => false,
                    ]);
            }

            // Solo una tarjeta activa
            if ($tipo === EquipoArchivo::TIPO_TARJETA) {
                $equipo->archivos()
                    ->where('tipo', EquipoArchivo::TIPO_TARJETA)
                    ->where('activo', true)
                    ->update([
                        'activo' => false,
                    ]);
            }

            // Solo una factura activa
            if ($tipo === EquipoArchivo::TIPO_FACTURA) {
                $equipo->archivos()
                    ->where('tipo', EquipoArchivo::TIPO_FACTURA)
                    ->where('activo', true)
                    ->update([
                        'activo' => false,
                    ]);
            }

            $nombreOriginal = $file->getClientOriginalName();
            $nombreArchivo = Str::uuid()->toString() . '.' . $extension;
            $carpeta = "equipos/{$equipo->id}/{$tipo}";
            $ruta = $file->storeAs($carpeta, $nombreArchivo, 'local');

            $archivo = EquipoArchivo::create([
                'equipo_id' => $equipo->id,
                'tipo' => $tipo,
                'nombre_original' => $nombreOriginal,
                'nombre_archivo' => $nombreArchivo,
                'ruta' => $ruta,
                'mime_type' => $mimeType,
                'extension' => $extension,
                'tamano' => $file->getSize(),
                'es_imagen' => $esImagen,
                'es_principal' => $esPrincipal,
                'orden' => (int) $request->input('orden', 0),
                'activo' => true,
                'fecha_documento' => $request->input('fecha_documento'),
                'fecha_vencimiento' => $request->input('fecha_vencimiento'),
                'observaciones' => $request->input('observaciones'),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Archivo subido correctamente.',
                'data' => [
                    'id' => $archivo->id,
                    'equipo_id' => $archivo->equipo_id,
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
                    'url_ver' => route('equipos.archivos.ver', [
                        'equipo' => $equipo->id,
                        'archivo' => $archivo->id,
                    ]),
                    'url_descargar' => route('equipos.archivos.descargar', [
                        'equipo' => $equipo->id,
                        'archivo' => $archivo->id,
                    ]),
                ]
            ], Response::HTTP_CREATED);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Ocurrió un error al subir el archivo.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Actualizar metadatos
     */
    public function update(Request $request, Equipo $equipo, EquipoArchivo $archivo)
    {
        $this->validarArchivoPerteneceAEquipo($equipo, $archivo);

        $request->validate([
            'tipo' => 'nullable|string|in:' . implode(',', EquipoArchivo::TIPOS_VALIDOS),
            'fecha_documento' => 'nullable|date',
            'fecha_vencimiento' => 'nullable|date',
            'observaciones' => 'nullable|string|max:1000',
            'orden' => 'nullable|integer|min:0',
            'activo' => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {
            $nuevoTipo = (string) $request->input('tipo', $archivo->tipo);
            $nuevoActivo = $request->has('activo')
                ? (bool) $request->input('activo')
                : (bool) $archivo->activo;

            if ($nuevoTipo === EquipoArchivo::TIPO_IMAGEN_PRINCIPAL && !$archivo->es_imagen) {
                return response()->json([
                    'message' => 'La imagen principal debe ser un archivo de imagen.'
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Si lo cambian a imagen principal, apagar las demás
            if ($nuevoTipo === EquipoArchivo::TIPO_IMAGEN_PRINCIPAL && $nuevoActivo) {
                $equipo->archivos()
                    ->where('id', '<>', $archivo->id)
                    ->where('tipo', EquipoArchivo::TIPO_IMAGEN_PRINCIPAL)
                    ->where('activo', true)
                    ->update([
                        'es_principal' => false,
                        'activo' => false,
                    ]);
            }

            // Si lo dejan activo como tarjeta, apagar otras
            if ($nuevoTipo === EquipoArchivo::TIPO_TARJETA && $nuevoActivo) {
                $equipo->archivos()
                    ->where('id', '<>', $archivo->id)
                    ->where('tipo', EquipoArchivo::TIPO_TARJETA)
                    ->where('activo', true)
                    ->update([
                        'activo' => false,
                    ]);
            }

            // Si lo dejan activo como factura, apagar otras
            if ($nuevoTipo === EquipoArchivo::TIPO_FACTURA && $nuevoActivo) {
                $equipo->archivos()
                    ->where('id', '<>', $archivo->id)
                    ->where('tipo', EquipoArchivo::TIPO_FACTURA)
                    ->where('activo', true)
                    ->update([
                        'activo' => false,
                    ]);
            }

            $archivo->tipo = $nuevoTipo;
            $archivo->fecha_documento = $request->input('fecha_documento', $archivo->fecha_documento);
            $archivo->fecha_vencimiento = $request->input('fecha_vencimiento', $archivo->fecha_vencimiento);
            $archivo->observaciones = $request->input('observaciones', $archivo->observaciones);
            $archivo->orden = $request->input('orden', $archivo->orden);
            $archivo->activo = $nuevoActivo;
            $archivo->es_principal = $nuevoTipo === EquipoArchivo::TIPO_IMAGEN_PRINCIPAL;

            $archivo->save();

            DB::commit();

            return response()->json([
                'message' => 'Archivo actualizado correctamente.',
                'data' => [
                    'id' => $archivo->id,
                    'equipo_id' => $archivo->equipo_id,
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
                    'url_ver' => route('equipos.archivos.ver', [
                        'equipo' => $equipo->id,
                        'archivo' => $archivo->id,
                    ]),
                    'url_descargar' => route('equipos.archivos.descargar', [
                        'equipo' => $equipo->id,
                        'archivo' => $archivo->id,
                    ]),
                ]
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'No se pudo actualizar el archivo.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Ver archivo inline
     */
    public function ver(Equipo $equipo, EquipoArchivo $archivo)
    {
        $this->validarArchivoPerteneceAEquipo($equipo, $archivo);

        if (!Storage::disk('local')->exists($archivo->ruta)) {
            abort(404, 'Archivo no encontrado.');
        }

        $fullPath = Storage::disk('local')->path($archivo->ruta);

        return response()->file($fullPath, [
            'Content-Type' => $archivo->mime_type ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="' . addslashes($archivo->nombre_original) . '"',
        ]);
    }

    /**
     * Descargar archivo
     */
    public function descargar(Equipo $equipo, EquipoArchivo $archivo)
    {
        $this->validarArchivoPerteneceAEquipo($equipo, $archivo);

        if (!Storage::disk('local')->exists($archivo->ruta)) {
            abort(404, 'Archivo no encontrado.');
        }

        $fullPath = Storage::disk('local')->path($archivo->ruta);

        return response()->download($fullPath, $archivo->nombre_original);
    }

    /**
     * Eliminar archivo
     */
    public function destroy(Equipo $equipo, EquipoArchivo $archivo)
    {
        $this->validarArchivoPerteneceAEquipo($equipo, $archivo);

        DB::beginTransaction();

        try {
            if (Storage::disk('local')->exists($archivo->ruta)) {
                Storage::disk('local')->delete($archivo->ruta);
            }

            $archivo->delete();

            DB::commit();

            return response()->json([
                'message' => 'Archivo eliminado correctamente.'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'No se pudo eliminar el archivo.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Validar que el archivo sí pertenece al equipo
     */
    private function validarArchivoPerteneceAEquipo(Equipo $equipo, EquipoArchivo $archivo): void
    {
        if ((int) $archivo->equipo_id !== (int) $equipo->id) {
            abort(404, 'Archivo no encontrado para este equipo.');
        }
    }
}