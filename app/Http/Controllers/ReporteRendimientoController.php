<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use App\Models\Carga;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReporteRendimientoController extends Controller
{
    public function show(Equipo $equipo, Request $request)
    {
        // Ajusta nombres de campos a los tuyos reales
        $raw = Carga::where('id_equipo', $equipo->id)
            ->orderBy('fecha', 'desc')
            ->take(6)                 // 6 cargas -> 5 intervalos
            ->get()
            ->sortBy('fecha')         // de antiguo a reciente para calcular deltas en orden
            ->values();

        if ($raw->count() < 2) {
            return response()->json([
                'equipo_id' => $equipo->id,
                'items' => [],
                'resumen' => null,
                'mensaje' => 'Se requieren al menos 2 cargas para calcular rendimiento.'
            ], 200);
        }

        // ¿Calcula por horas (horómetro) o por km (odómetro)?
        // unidad_rend === 1 -> horas (ajústalo si tus valores son otros)
        $usaHoras = false;
        foreach ($raw as $c) {
            if (!is_null($c->horometro) || (isset($equipo->unidad_rend) && (int)$equipo->unidad_rend === 1)) {
                $usaHoras = true;
                break;
            }
        }

        $items = [];

        for ($i = 1; $i < $raw->count(); $i++) {
            $prev = $raw[$i - 1];
            $curr = $raw[$i];

            $litros = (float) ($curr->litros ?? 0);
            if ($litros <= 0) {
                continue; // ignora registros sin litros válidos
            }

            if ($usaHoras) {
                $prevMed = $prev->horometro ?? null;
                $currMed = $curr->horometro ?? null;
                $delta   = (is_numeric($currMed) && is_numeric($prevMed)) ? (float)($currMed - $prevMed) : 0;
                $eficiencia = $delta > 0 ? $delta / $litros : null; // h/L
                $unidad = 'h/L';
            } else {
                // *** CORRECCIÓN: usar odómetro para km/L ***
                $prevMed = $prev->odometro ?? null;
                $currMed = $curr->odometro ?? null;
                $delta   = (is_numeric($currMed) && is_numeric($prevMed)) ? (float)($currMed - $prevMed) : 0;
                $eficiencia = $delta > 0 ? $delta / $litros : null; // km/L
                $unidad = 'km/L';
            }

            // Estado vs rango del equipo
            $estado = null;
            if (!is_null($eficiencia)) {
                $inf = $equipo->rango_inferior ?? null;
                $sup = $equipo->rango_superior ?? null;
                if (is_numeric($inf) && is_numeric($sup)) {
                    if ($eficiencia < $inf)      $estado = 'bajo';
                    elseif ($eficiencia > $sup)  $estado = 'alto';
                    else                          $estado = 'en_rango';
                }
            }
            //$fecha = $curr->fecha ? Carbon::parse($curr->fecha)->format('Y-m-d') : null;
            $fecha = $curr->fecha->format('Y-m-d');
            $hora  = $curr->hora  ? substr($curr->hora, 11, 5) : null; // "HH:MM"
            $fechora = $fecha .'T'.$hora;


            $items[] = [
                'fecha_carga'        => $fechora,
                'litros'             => $litros,
                // *** CORRECCIÓN: reportar medidores correctos según modo ***
                'medidor_prev'       => $prevMed,
                'medidor_act'        => $currMed,
                'distancia_o_horas'  => $delta > 0 ? $delta : null,
                'eficiencia'         => $eficiencia,
                'unidad'             => $unidad,
                'estado'             => $estado,
                // *** Nota: id_documento podría no existir; usa $curr->id si aplica ***
                'carga_id'           => $curr->id ?? ($curr->id_documento ?? null),
            ];
        }

        // Ordena por fecha y limita a los últimos 5 intervalos
        //$items = collect($items)->sortBy('fecha_carga')->values()->takeLast(5)->values();
        $items = collect($items)->sortBy('fecha_carga')->values()
            ->reverse()->take(5)->values()
            ->reverse()->values();


        // Si tras las validaciones no quedó ningún item, evita reventar y responde claro
        if ($items->count() === 0) {
            $unidadDefault = $usaHoras ? 'h/L' : 'km/L';
            return response()->json([
                'equipo_id'       => $equipo->id,
                'unidad'          => $unidadDefault,
                'rango_objetivo'  => [
                    'inferior' => $equipo->rango_inferior ?? null,
                    'superior' => $equipo->rango_superior ?? null,
                ],
                'items'           => [],
                'resumen'         => [
                    'promedio'              => null,
                    'min'                   => null,
                    'max'                   => null,
                    'porcentaje_en_rango'   => 0,
                    'tendencia'             => null,
                    'total_registros'       => 0,
                ],
                'mensaje' => 'Sin intervalos válidos: verifica litros > 0 y que el medidor aumente entre cargas.'
            ], 200);
        }

        $vals      = $items->pluck('eficiencia')->filter(fn($v) => !is_null($v))->values();
        $prom      = $vals->count() ? round($vals->avg(), 2) : null;
        $min       = $vals->count() ? round($vals->min(), 2) : null;
        $max       = $vals->count() ? round($vals->max(), 2) : null;
        $enRango   = $items->where('estado', 'en_rango')->count();
        $porcOK    = $items->count() ? round($enRango * 100 / $items->count()) : 0;

        // Tendencia (regresión lineal simple)
        $slope = null;
        if ($vals->count() >= 3) {
            $n = $vals->count();
            $x = range(1, $n);
            $xMean = array_sum($x) / $n;
            $yMean = $vals->avg();
            $num = 0;
            $den = 0;
            for ($k = 0; $k < $n; $k++) {
                $num += ($x[$k] - $xMean) * ($vals[$k] - $yMean);
                $den += ($x[$k] - $xMean) * ($x[$k] - $xMean);
            }
            $slope = $den > 0 ? round($num / $den, 4) : null;
        }

        // *** CORRECCIÓN: no tocar $items->first()['unidad'] ***
        $unidadFinal = $usaHoras ? 'h/L' : 'km/L';

        return response()->json([
            'equipo_id'      => $equipo->id,
            'unidad'         => $unidadFinal,
            'rango_objetivo' => [
                'inferior' => $equipo->rango_inferior ?? null,
                'superior' => $equipo->rango_superior ?? null,
            ],
            'items'   => $items,
            'resumen' => [
                'promedio'             => $prom,
                'min'                  => $min,
                'max'                  => $max,
                'porcentaje_en_rango'  => $porcOK,
                'tendencia'            => $slope,
                'total_registros'      => $items->count(),
            ],
        ], 200);
    }
}
