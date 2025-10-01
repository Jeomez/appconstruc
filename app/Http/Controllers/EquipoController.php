<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;



class EquipoController extends Controller
{
    public function showByNumeco(string $numeco)
    {
        // Ajusta el nombre de columna si no es 'numeco'
        $equipo = Equipo::where('numeco', $numeco)->first();

        if (!$equipo) {
            return response()->json(['message' => 'Equipo no encontrado'], 404);
        }

        
        return response()->json([
            'id'             => $equipo->id,
            'desc_equipo'    => $equipo->descripcion ?? $equipo->nombre ?? '',
            'id_combustible' => $equipo->combustible ?? $equipo->combustible ?? null,
            'noserie' => $equipo->noserie ?? $equipo->noserie ?? null,
            'peso' => $equipo->peso ?? $equipo->peso ?? null,
        ]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Equipo::all();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Log::info('Payload recibido', $request->all());

        $equipo = Equipo::create($request->all());
        return response()->json($equipo, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        return Equipo::findOrFail($id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Equipo $equipo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $equipo = Equipo::findOrFail($id);
        $equipo->update($request->all());
        return response()->json($equipo, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Equipo::destroy($id);
        return response()->json(null, 204);
    }
}
