<?php

namespace App\Http\Controllers;

use App\Models\EquipoMedicionesEvento;
use App\Models\Equipo;
use Illuminate\Http\Request;

class EquipoMedicionesEventoController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = $request->user()->empresa_id;

        $q = EquipoMedicionesEvento::query()
            ->with('equipo')
            ->where('empresa_id', $empresaId);

        if ($request->filled('equipo_id')) {
            $q->where('equipo_id', (int) $request->equipo_id);
        }

        return response()->json($q->orderByDesc('fecha')->paginate(20));
    }

    public function store(Request $request)
    {
        $empresaId = $request->user()->empresa_id;

        $data = $request->validate([
            'equipo_id' => ['required', 'exists:equipos,id'],

            'km_antes' => ['nullable', 'integer', 'min:0'],
            'km_despues' => ['nullable', 'integer', 'min:0'],

            'horas_antes' => ['nullable', 'integer', 'min:0'],
            'horas_despues' => ['nullable', 'integer', 'min:0'],

            'motivo' => ['nullable', 'string', 'max:255'],
            'fecha' => ['required', 'date'],
        ]);

        $equipoOk = Equipo::where('id', $data['equipo_id'])
            ->where('empresa_id', $empresaId)
            ->exists();

        if (!$equipoOk) {
            return response()->json(['message' => 'Equipo inválido'], 422);
        }

        $hayKm = !is_null($data['km_antes'] ?? null) || !is_null($data['km_despues'] ?? null);
        $hayHoras = !is_null($data['horas_antes'] ?? null) || !is_null($data['horas_despues'] ?? null);

        if (!$hayKm && !$hayHoras) {
            return response()->json(['message' => 'Debes capturar datos de km o de horas (antes/después).'], 422);
        }

        if ($hayKm && (is_null($data['km_antes'] ?? null) || is_null($data['km_despues'] ?? null))) {
            return response()->json(['message' => 'Si capturas km, debes enviar km_antes y km_despues.'], 422);
        }

        if ($hayHoras && (is_null($data['horas_antes'] ?? null) || is_null($data['horas_despues'] ?? null))) {
            return response()->json(['message' => 'Si capturas horas, debes enviar horas_antes y horas_despues.'], 422);
        }

        $data['empresa_id'] = $empresaId;

        $evento = EquipoMedicionesEvento::create($data);

        return response()->json($evento->load('equipo'), 201);
    }

    public function show(Request $request, EquipoMedicionesEvento $equipo_mediciones_evento)
    {
        if ($equipo_mediciones_evento->empresa_id !== $request->user()->empresa_id) abort(404);
        return response()->json($equipo_mediciones_evento->load('equipo'));
    }

    public function update(Request $request, EquipoMedicionesEvento $equipo_mediciones_evento)
    {
        if ($equipo_mediciones_evento->empresa_id !== $request->user()->empresa_id) abort(404);

        $data = $request->validate([
            'equipo_id' => ['sometimes', 'exists:equipos,id'],

            'km_antes' => ['nullable', 'integer', 'min:0'],
            'km_despues' => ['nullable', 'integer', 'min:0'],

            'horas_antes' => ['nullable', 'integer', 'min:0'],
            'horas_despues' => ['nullable', 'integer', 'min:0'],

            'motivo' => ['nullable', 'string', 'max:255'],
            'fecha' => ['sometimes', 'date'],
        ]);

        if (isset($data['equipo_id'])) {
            $equipoOk = Equipo::where('id', $data['equipo_id'])
                ->where('empresa_id', $request->user()->empresa_id)
                ->exists();
            if (!$equipoOk) return response()->json(['message' => 'Equipo inválido'], 422);
        }

        $equipo_mediciones_evento->update($data);

        return response()->json($equipo_mediciones_evento->load('equipo'));
    }

    public function destroy(Request $request, EquipoMedicionesEvento $equipo_mediciones_evento)
    {
        if ($equipo_mediciones_evento->empresa_id !== $request->user()->empresa_id) abort(404);

        $equipo_mediciones_evento->delete();

        return response()->json(['ok' => true]);
    }
}
