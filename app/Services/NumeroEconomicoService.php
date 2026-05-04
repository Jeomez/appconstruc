<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class NumeroEconomicoService
{
    public function previewPorTipoYClave(int $tipoId, string $clave): array
    {
        $prefijo = $this->prefijoPorTipoId($tipoId);
        $clave = $this->normalizarClave($clave);
        $llave = $this->armarLlave($prefijo, $clave);

        $row = DB::table('equipo_consecutivos')
            ->where('tipo', $llave)
            ->first();

        $ultimo = $row ? (int) $row->ultimo : 0;
        $nuevo = $ultimo + 1;
        $consecutivo = $this->formatearConsecutivo($nuevo);

        return [
            'prefijo' => $prefijo,
            'clave' => $clave,
            'consecutivo' => $consecutivo,
            'numeco' => $this->formatear($prefijo, $clave, $nuevo),
        ];
    }

    public function generarPorTipoYClave(int $tipoId, string $clave): string
    {
        $prefijo = $this->prefijoPorTipoId($tipoId);
        $clave = $this->normalizarClave($clave);
        $llave = $this->armarLlave($prefijo, $clave);

        return DB::transaction(function () use ($llave, $prefijo, $clave) {
            $row = DB::table('equipo_consecutivos')
                ->where('tipo', $llave)
                ->lockForUpdate()
                ->first();

            $ultimo = 0;

            if (!$row) {
                DB::table('equipo_consecutivos')->insert([
                    'tipo' => $llave,
                    'ultimo' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $ultimo = (int) $row->ultimo;
            }

            $nuevo = $ultimo + 1;

            DB::table('equipo_consecutivos')
                ->where('tipo', $llave)
                ->update([
                    'ultimo' => $nuevo,
                    'updated_at' => now(),
                ]);

            return $this->formatear($prefijo, $clave, $nuevo);
        });
    }

    private function prefijoPorTipoId(int $tipoId): string
    {
        return match ($tipoId) {
            1 => 'VE',
            2 => 'CA',
            3 => 'MA',
            4 => 'EQ',
            default => throw new InvalidArgumentException('Tipo de equipo inválido'),
        };
    }

    private function normalizarClave(string $clave): string
    {
        $clave = strtoupper(trim($clave));
        $clave = preg_replace('/\s+/', '', $clave);
        $clave = substr($clave, 0, 4);

        if ($clave === '') {
            throw new InvalidArgumentException('La clave es requerida');
        }

        return $clave;
    }

    private function armarLlave(string $prefijo, string $clave): string
    {
        return $prefijo . '-' . $clave;
    }

    private function formatear(string $prefijo, string $clave, int $consecutivo): string
    {
        return $prefijo . '-' . $clave . '-' . $this->formatearConsecutivo($consecutivo);
    }

    private function formatearConsecutivo(int $consecutivo): string
    {
        return str_pad((string) $consecutivo, 2, '0', STR_PAD_LEFT);
    }
}