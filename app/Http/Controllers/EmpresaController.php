<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use Illuminate\Http\Request;

class EmpresaController extends Controller
{
    public function index(Request $request)
    {
        $q = Empresa::query();

        if ($request->filled('activo')) {
            $q->where('activo', $request->activo);
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $q->where(function ($qq) use ($s) {
                $qq->where('nombre', 'like', "%{$s}%")
                   ->orWhere('rfc', 'like', "%{$s}%")
                   ->orWhere('telefono', 'like', "%{$s}%");
            });
        }

        return response()->json(
            //$q->orderBy('id', 'desc')->paginate($request->integer('per_page', 15))
            $q->orderBy('id', 'desc')->get()
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'    => ['required', 'string', 'max:255'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'cp'        => ['nullable', 'string', 'max:10'],
            'rfc'       => ['nullable', 'string', 'max:20'],
            'telefono'  => ['nullable', 'string', 'max:20'],
            'activo'    => ['nullable', 'string'],
        ]);

        $empresa = Empresa::create($data);

        return response()->json($empresa, 201);
    }

    public function show(Empresa $empresa)
    {
        return response()->json($empresa);
    }

    public function update(Request $request, Empresa $empresa)
    {
        $data = $request->validate([
            'nombre'    => ['sometimes', 'required', 'string', 'max:255'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'cp'        => ['nullable', 'string', 'max:10'],
            'rfc'       => ['nullable', 'string', 'max:20'],
            'telefono'  => ['nullable', 'string', 'max:20'],
            'activo'    => ['nullable', 'string'],
        ]);

        $empresa->update($data);

        return response()->json($empresa);
    }

    public function destroy(Empresa $empresa)
    {
        $empresa->delete();
        return response()->json(['message' => 'Empresa eliminada']);
    }
}
