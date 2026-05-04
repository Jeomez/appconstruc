<?php

namespace App\Http\Controllers;

use App\Models\Servicio;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServicioController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = $request->user()->empresa_id;

        $q = Servicio::query()
            ->where('empresa_id', $empresaId);

        if ($request->filled('tipo')) {
            $q->where('tipo', $request->string('tipo'));
        }

        if ($request->filled('activo')) {
            $q->where('activo', filter_var($request->activo, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $q->where('nombre', 'like', "%{$search}%");
        }

        return response()->json(
            $q->orderBy('nombre')->paginate(20)
        );
    }

    public function store(Request $request)
    {
        $empresaId = $request->user()->empresa_id;

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'tipo' => ['required', Rule::in(['servicio', 'reparacion'])],
            'descripcion' => ['nullable', 'string'],
            'activo' => ['sometimes', 'boolean'],
        ]);

        $data['empresa_id'] = $empresaId;

        $servicio = Servicio::create($data);

        return response()->json($servicio, 201);
    }

    public function show(Request $request, Servicio $servicio)
    {
        if ($servicio->empresa_id !== $request->user()->empresa_id) {
            abort(404);
        }

        return response()->json($servicio);
    }

    public function update(Request $request, Servicio $servicio)
    {
        if ($servicio->empresa_id !== $request->user()->empresa_id) {
            abort(404);
        }

        $data = $request->validate([
            'nombre' => ['sometimes', 'string', 'max:255'],
            'tipo' => ['sometimes', Rule::in(['preventivo', 'correctivo'])],
            'descripcion' => ['nullable', 'string'],
            'activo' => ['sometimes', 'boolean'],
        ]);

        $servicio->update($data);

        return response()->json($servicio);
    }

    public function destroy(Request $request, Servicio $servicio)
    {
        if ($servicio->empresa_id !== $request->user()->empresa_id) {
            abort(404);
        }

        $servicio->update(['activo' => false]);

        return response()->json(['ok' => true]);
    }
}
