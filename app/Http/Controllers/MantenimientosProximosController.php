<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use App\Models\MantenimientoRegla;
use App\Models\MantenimientoRealizado;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MantenimientosProximosController extends Controller
{
    public function show(Request $request, Equipo $equipo)
    {
        $empresaId = $request->user()->empresa_id;

        // 🔒 Seguridad multiempresa
        if ($equipo->empresa_id !== $empresaId) {
            abort(404);
        }

        $kmActual = $request->query('km_actual');
        $horasActual = $request->query('horas_actual');

        if (is_null($kmActual) && is_null($horasActual)) {
            return response()->json([
                'message' => 'Debes enviar km_actual o horas_actual'
            ], 422);
        }

        $reglas = MantenimientoRegla::where('equipo_id', $equipo->id)
            ->where('empresa_id', $empresaId)
            ->where('activo', true)
            ->with('servicio')
            ->get();

        $items = [];
        $vencidos = 0;
        $porVencer = 0;
        $ok = 0;

        foreach ($reglas as $regla) {

            $ultimo = MantenimientoRealizado::where('equipo_id', $equipo->id)
                ->where('servicio_id', $regla->servicio_id)
                ->where('empresa_id', $empresaId)
                ->orderByDesc('fecha')
                ->first();

            $restanteKm = null;
            $restanteHoras = null;
            $restanteDias = null;
            $estado = 'ok';

            // KM
            if ($regla->cada_km && $kmActual !== null) {
                $baseKm = $ultimo?->km_actual ?? $regla->kilometraje_inicial ?? 0;
                $restanteKm = ($baseKm + $regla->cada_km) - $kmActual;

                if ($restanteKm <= 0) {
                    $estado = 'vencidos';
                } elseif ($restanteKm <= ($regla->cada_km * 0.1)) {
                    $estado = 'por_vencer';
                }
            }

            // HORAS
            if ($regla->cada_horas && $horasActual !== null) {
                $baseHoras = $ultimo?->horas_actual ?? $regla->horas_inicial ?? 0;
                $restanteHoras = ($baseHoras + $regla->cada_horas) - $horasActual;

                if ($restanteHoras <= 0) {
                    $estado = 'vencidos';
                } elseif ($restanteHoras <= ($regla->cada_horas * 0.1)) {
                    $estado = 'por_vencer';
                }
            }

            // DÍAS
            if ($regla->cada_dias) {
                $baseFecha = $ultimo?->fecha ?? $regla->fecha_inicio ?? now();
                $proximo = Carbon::parse($baseFecha)->addDays($regla->cada_dias);
                $restanteDias = now()->diffInDays($proximo, false);

                if ($restanteDias <= 0) {
                    $estado = 'vencidos';
                } elseif ($restanteDias <= 7) {
                    $estado = 'por_vencer';
                }
            }

            if ($estado === 'vencidos') $vencidos++;
            elseif ($estado === 'por_vencer') $porVencer++;
            else $ok++;

            $items[] = [
                'servicio' => $regla->servicio,
                'regla' => $regla,
                'ultimo' => $ultimo,
                'restante' => [
                    'km' => $restanteKm,
                    'horas' => $restanteHoras,
                    'dias' => $restanteDias,
                ],
                'estado' => $estado,
            ];
        }

        return response()->json([
            'equipo_id' => $equipo->id,
            'resumen' => [
                'vencidos' => $vencidos,
                'por_vencer' => $porVencer,
                'ok' => $ok,
            ],
            'items' => $items,
        ]);
    }
}
