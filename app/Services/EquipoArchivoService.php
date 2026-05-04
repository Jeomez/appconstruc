<?php

namespace App\Services;

use App\Models\EquipoArchivo;
use App\Models\EquipoArchivoTemp;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EquipoArchivoService
{
    protected string $disk = 'local';

    public function listarTemporales(string $uploadToken)
    {
        return EquipoArchivoTemp::where('upload_token', $uploadToken)
            ->orderBy('orden')
            ->orderByDesc('id')
            ->get();
    }

    public function guardarTemporal(UploadedFile $file, array $data): EquipoArchivoTemp
    {
        $tipo = (string) $data['tipo'];
        $uploadToken = (string) $data['upload_token'];

        $mimeType = $file->getMimeType();
        $extension = strtolower($file->getClientOriginalExtension());
        $esImagen = Str::startsWith((string) $mimeType, 'image/');
        $esPrincipal = $tipo === EquipoArchivo::TIPO_IMAGEN_PRINCIPAL;

        $this->validarTipoArchivo($tipo, $esImagen, $mimeType, $esPrincipal);

        DB::beginTransaction();

        try {
            // Solo una imagen principal activa por token
            if ($tipo === EquipoArchivo::TIPO_IMAGEN_PRINCIPAL) {
                EquipoArchivoTemp::where('upload_token', $uploadToken)
                    ->where('tipo', EquipoArchivo::TIPO_IMAGEN_PRINCIPAL)
                    ->where('activo', true)
                    ->update([
                        'es_principal' => false,
                        'activo' => false,
                    ]);
            }

            // Solo una tarjeta activa por token
            if ($tipo === EquipoArchivo::TIPO_TARJETA) {
                EquipoArchivoTemp::where('upload_token', $uploadToken)
                    ->where('tipo', EquipoArchivo::TIPO_TARJETA)
                    ->where('activo', true)
                    ->update([
                        'activo' => false,
                    ]);
            }

            // Solo una factura activa por token
            if ($tipo === EquipoArchivo::TIPO_FACTURA) {
                EquipoArchivoTemp::where('upload_token', $uploadToken)
                    ->where('tipo', EquipoArchivo::TIPO_FACTURA)
                    ->where('activo', true)
                    ->update([
                        'activo' => false,
                    ]);
            }

            $nombreOriginal = $file->getClientOriginalName();
            $nombreArchivo = Str::uuid()->toString() . '.' . $extension;
            $carpeta = "equipos/temp/{$uploadToken}/{$tipo}";
            $ruta = $file->storeAs($carpeta, $nombreArchivo, $this->disk);

            $archivo = EquipoArchivoTemp::create([
                'upload_token' => $uploadToken,
                'tipo' => $tipo,
                'nombre_original' => $nombreOriginal,
                'nombre_archivo' => $nombreArchivo,
                'ruta' => $ruta,
                'mime_type' => $mimeType,
                'extension' => $extension,
                'tamano' => $file->getSize(),
                'es_imagen' => $esImagen,
                'es_principal' => $esPrincipal,
                'orden' => (int) ($data['orden'] ?? 0),
                'activo' => true,
                'fecha_documento' => $data['fecha_documento'] ?? null,
                'fecha_vencimiento' => $data['fecha_vencimiento'] ?? null,
                'observaciones' => $data['observaciones'] ?? null,
            ]);

            DB::commit();

            return $archivo;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function eliminarTemporal(int $id): void
    {
        $archivo = EquipoArchivoTemp::findOrFail($id);

        DB::beginTransaction();

        try {
            if (Storage::disk($this->disk)->exists($archivo->ruta)) {
                Storage::disk($this->disk)->delete($archivo->ruta);
            }

            $archivo->delete();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function asociarTemporalesAEquipo(string $uploadToken, int $equipoId): void
    {
        $temporales = EquipoArchivoTemp::where('upload_token', $uploadToken)
            ->orderBy('orden')
            ->orderBy('id')
            ->get();

        if ($temporales->isEmpty()) {
            return;
        }

        DB::beginTransaction();

        try {
            foreach ($temporales as $temp) {
                // Mantener reglas de unicidad activas en definitivos
                if ($temp->activo && $temp->tipo === EquipoArchivo::TIPO_IMAGEN_PRINCIPAL) {
                    EquipoArchivo::where('equipo_id', $equipoId)
                        ->where('tipo', EquipoArchivo::TIPO_IMAGEN_PRINCIPAL)
                        ->where('activo', true)
                        ->update([
                            'es_principal' => false,
                            'activo' => false,
                        ]);
                }

                if ($temp->activo && $temp->tipo === EquipoArchivo::TIPO_TARJETA) {
                    EquipoArchivo::where('equipo_id', $equipoId)
                        ->where('tipo', EquipoArchivo::TIPO_TARJETA)
                        ->where('activo', true)
                        ->update([
                            'activo' => false,
                        ]);
                }

                if ($temp->activo && $temp->tipo === EquipoArchivo::TIPO_FACTURA) {
                    EquipoArchivo::where('equipo_id', $equipoId)
                        ->where('tipo', EquipoArchivo::TIPO_FACTURA)
                        ->where('activo', true)
                        ->update([
                            'activo' => false,
                        ]);
                }

                $rutaNueva = "equipos/{$equipoId}/{$temp->tipo}/{$temp->nombre_archivo}";

                if (Storage::disk($this->disk)->exists($temp->ruta)) {
                    Storage::disk($this->disk)->makeDirectory("equipos/{$equipoId}/{$temp->tipo}");
                    Storage::disk($this->disk)->move($temp->ruta, $rutaNueva);
                } else {
                    // Si no existe físicamente, omitimos ese temporal para no romper todo el guardado
                    continue;
                }

                EquipoArchivo::create([
                    'equipo_id' => $equipoId,
                    'tipo' => $temp->tipo,
                    'nombre_original' => $temp->nombre_original,
                    'nombre_archivo' => $temp->nombre_archivo,
                    'ruta' => $rutaNueva,
                    'mime_type' => $temp->mime_type,
                    'extension' => $temp->extension,
                    'tamano' => $temp->tamano,
                    'es_imagen' => (bool) $temp->es_imagen,
                    'es_principal' => (bool) $temp->es_principal,
                    'orden' => (int) $temp->orden,
                    'activo' => (bool) $temp->activo,
                    'fecha_documento' => $temp->fecha_documento,
                    'fecha_vencimiento' => $temp->fecha_vencimiento,
                    'observaciones' => $temp->observaciones,
                ]);

                $temp->delete();
            }

            $dirTemp = "equipos/temp/{$uploadToken}";
            if (Storage::disk($this->disk)->exists($dirTemp)) {
                Storage::disk($this->disk)->deleteDirectory($dirTemp);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function validarTipoArchivo(string $tipo, bool $esImagen, ?string $mimeType, bool $esPrincipal): void
    {
        if ($esPrincipal && !$esImagen) {
            abort(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                'La imagen principal debe ser un archivo de imagen.'
            );
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
            abort(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                'Ese tipo de documento solo permite PDF o imágenes.'
            );
        }
    }
}