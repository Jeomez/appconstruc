<?php

namespace App\Http\Controllers;

use App\Models\MantenimientoRealizado;
use App\Models\Equipo;
use Illuminate\Http\Request;

class MantenimientoRealizadoController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = $request->user()->empresa_id;

        $q = MantenimientoRealizado::query()
            ->with(['equipo', 'servicio'])
            ->where('empresa_id', $empresaId);

        if ($request->filled('equipo_id')) {
            $q->where('equipo_id', (int) $request->equipo_id);
        }

        if ($request->filled('servicio_id')) {
            $q->where('servicio_id', (int) $request->servicio_id);
        }

        if ($request->filled('desde')) {
            $q->whereDate('fecha', '>=', $request->input('desde'));
        }

        if ($request->filled('hasta')) {
            $q->whereDate('fecha', '<=', $request->input('hasta'));
        }

        return response()->json($q->orderByDesc('fecha')->paginate(20));
    }

    public function store(Request $request)
    {
        $empresaId = $request->user()->empresa_id;

        $data = $request->validate([
            'equipo_id' => ['required', 'exists:equipos,id'],
            'servicio_id' => ['required', 'exists:servicios,id'],
            'fecha' => ['required', 'date'],

            'km_actual' => ['nullable', 'integer', 'min:0'],
            'horas_actual' => ['nullable', 'integer', 'min:0'],

            'notas' => ['nullable', 'string'],
            'costo' => ['nullable', 'numeric', 'min:0'],
        ]);

        $equipoOk = Equipo::where('id', $data['equipo_id'])
            ->where('empresa_id', $empresaId)
            ->exists();

        if (!$equipoOk) {
            return response()->json(['message' => 'Equipo inválido'], 422);
        }

        if (is_null($data['km_actual'] ?? null) && is_null($data['horas_actual'] ?? null)) {
            return response()->json([
                'message' => 'Debes enviar al menos km_actual o horas_actual.'
            ], 422);
        }

        $data['empresa_id'] = $empresaId;

        $item = MantenimientoRealizado::create($data);

        return response()->json($item->load(['equipo', 'servicio']), 201);
    }

    public function show(Request $request, MantenimientoRealizado $mantenimiento_realizado)
    {
        if ($mantenimiento_realizado->empresa_id !== $request->user()->empresa_id) abort(404);
        return response()->json($mantenimiento_realizado->load(['equipo', 'servicio']));
    }

    public function update(Request $request, MantenimientoRealizado $mantenimiento_realizado)
    {
        if ($mantenimiento_realizado->empresa_id !== $request->user()->empresa_id) abort(404);

        $data = $request->validate([
            'equipo_id' => ['sometimes', 'exists:equipos,id'],
            'servicio_id' => ['sometimes', 'exists:servicios,id'],
            'fecha' => ['sometimes', 'date'],

            'km_actual' => ['nullable', 'integer', 'min:0'],
            'horas_actual' => ['nullable', 'integer', 'min:0'],

            'notas' => ['nullable', 'string'],
            'costo' => ['nullable', 'numeric', 'min:0'],
        ]);

        if (isset($data['equipo_id'])) {
            $equipoOk = Equipo::where('id', $data['equipo_id'])
                ->where('empresa_id', $request->user()->empresa_id)
                ->exists();
            if (!$equipoOk) return response()->json(['message' => 'Equipo inválido'], 422);
        }

        $mantenimiento_realizado->update($data);

        return response()->json($mantenimiento_realizado->load(['equipo', 'servicio']));
    }

    public function destroy(Request $request, MantenimientoRealizado $mantenimiento_realizado)
    {
        if ($mantenimiento_realizado->empresa_id !== $request->user()->empresa_id) abort(404);

        $mantenimiento_realizado->delete();

        return response()->json(['ok' => true]);
    }
}
