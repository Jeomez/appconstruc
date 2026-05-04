<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RolController extends Controller
{
    public function index(Request $request)
    {
        $q = Rol::query();

        if ($request->filled('activo')) {
            $q->where('activo', $request->activo);
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $q->where(function ($qq) use ($s) {
                $qq->where('nombre', 'like', "%{$s}%")
                   ->orWhere('idstr', 'like', "%{$s}%");
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
            'idstr'  => ['required', 'string', 'max:50', 'regex:/^[A-Z0-9_]+$/', 'unique:roles,idstr'],
            'nombre' => ['required', 'string', 'max:255'],
            'activo' => ['nullable', 'string'],
        ]);

        $rol = Rol::create($data);

        return response()->json($rol, 201);
    }

    public function show(Rol $rol)
    {
        return response()->json($rol);
    }

    public function update(Request $request, Rol $rol)
    {
        $data = $request->validate([
            'idstr'  => [
                'sometimes', 'required', 'string', 'max:50', 'regex:/^[A-Z0-9_]+$/',
                Rule::unique('roles', 'idstr')->ignore($rol->id),
            ],
            'nombre' => ['sometimes', 'required', 'string', 'max:255'],
            'activo' => ['nullable', 'string'],
        ]);

        $rol->update($data);

        return response()->json($rol);
    }

    public function destroy(Rol $rol)
    {
        $rol->delete();
        return response()->json(['message' => 'Rol eliminado']);
    }
}
