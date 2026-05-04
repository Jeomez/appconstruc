<?php

namespace App\Http\Controllers;

use App\Models\MantenimientoRegla;
use App\Models\Equipo;
use Illuminate\Http\Request;

class MantenimientoReglaController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = $request->user()->empresa_id;

        $q = MantenimientoRegla::query()
            ->with(['equipo', 'servicio'])
            ->where('empresa_id', $empresaId);

        if ($request->filled('equipo_id')) {
            $q->where('equipo_id', (int) $request->equipo_id);
        }

        if ($request->filled('servicio_id')) {
            $q->where('servicio_id', (int) $request->servicio_id);
        }

        if ($request->filled('activo')) {
            $q->where('activo', filter_var($request->activo, FILTER_VALIDATE_BOOLEAN));
        }

        return response()->json($q->latest()->paginate(20));
    }

    public function store(Request $request)
    {
        $empresaId = $request->user()->empresa_id;

        $data = $request->validate([
            'equipo_id' => ['required', 'exists:equipos,id'],
            'servicio_id' => ['required', 'exists:servicios,id'],

            'cada_km' => ['nullable', 'integer', 'min:1'],
            'cada_horas' => ['nullable', 'integer', 'min:1'],
            'cada_dias' => ['nullable', 'integer', 'min:1'],

            'kilometraje_inicial' => ['nullable', 'integer', 'min:0'],
            'horas_inicial' => ['nullable', 'integer', 'min:0'],
            'fecha_inicio' => ['nullable', 'date'],

            'activo' => ['sometimes', 'boolean'],
        ]);

        // Validar que el equipo sea de mi empresa
        $equipoOk = Equipo::where('id', $data['equipo_id'])
            ->where('empresa_id', $empresaId)
            ->exists();

        if (!$equipoOk) {
            return response()->json(['message' => 'Equipo inválido'], 422);
        }

        // Regla mínima: debe tener AL MENOS 1 periodicidad
        if (empty($data['cada_km']) && empty($data['cada_horas']) && empty($data['cada_dias'])) {
            return response()->json([
                'message' => 'La regla debe tener al menos una periodicidad: cada_km, cada_horas o cada_dias.'
            ], 422);
        }
        
        $data['empresa_id'] = $empresaId;

        $regla = MantenimientoRegla::create($data);

        return response()->json($regla->load(['equipo', 'servicio']), 201);
    }

    public function show(Request $request, MantenimientoRegla $mantenimiento_regla)
    {
        if ($mantenimiento_regla->empresa_id !== $request->user()->empresa_id) abort(404);
        return response()->json($mantenimiento_regla->load(['equipo', 'servicio']));
    }

    public function update(Request $request, MantenimientoRegla $mantenimiento_regla)
    {
        if ($mantenimiento_regla->empresa_id !== $request->user()->empresa_id) abort(404);

        $data = $request->validate([
            'equipo_id' => ['sometimes', 'exists:equipos,id'],
            'servicio_id' => ['sometimes', 'exists:servicios,id'],

            'cada_km' => ['nullable', 'integer', 'min:1'],
            'cada_horas' => ['nullable', 'integer', 'min:1'],
            'cada_dias' => ['nullable', 'integer', 'min:1'],

            'kilometraje_inicial' => ['nullable', 'integer', 'min:0'],
            'horas_inicial' => ['nullable', 'integer', 'min:0'],
            'fecha_inicio' => ['nullable', 'date'],

            'activo' => ['sometimes', 'boolean'],
        ]);

        // Si cambian equipo_id, validar empresa
        if (isset($data['equipo_id'])) {
            $equipoOk = Equipo::where('id', $data['equipo_id'])
                ->where('empresa_id', $request->user()->empresa_id)
                ->exists();
            if (!$equipoOk) return response()->json(['message' => 'Equipo inválido'], 422);
        }

        $mantenimiento_regla->update($data);

        if (
            is_null($mantenimiento_regla->cada_km) &&
            is_null($mantenimiento_regla->cada_horas) &&
            is_null($mantenimiento_regla->cada_dias)
        ) {
            return response()->json([
                'message' => 'La regla debe tener al menos una periodicidad: cada_km, cada_horas o cada_dias.'
            ], 422);
        }

        return response()->json($mantenimiento_regla->load(['equipo', 'servicio']));
    }

    public function destroy(Request $request, MantenimientoRegla $mantenimiento_regla)
    {
        if ($mantenimiento_regla->empresa_id !== $request->user()->empresa_id) abort(404);

        $mantenimiento_regla->update(['activo' => false]);

        return response()->json(['ok' => true]);
    }
}
