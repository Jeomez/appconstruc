<?php

namespace App\Http\Controllers;

use App\Models\Tipo_Equipo;
use Illuminate\Http\Request;

class TipoEquipoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index() {
        return Tipo_Equipo::all();
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
    public function store(Request $request) {
        $tipo_equipo = Tipo_Equipo::create($request->all());
        return response()->json($tipo_equipo, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id) {
        return Tipo_Equipo::findOrFail($id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tipo_Equipo $tipo_Equipo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {
        $tipo_equipo = Tipo_Equipo::findOrFail($id);
        $tipo_equipo->update($request->all());
        return response()->json($tipo_equipo, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {
        Tipo_Equipo::destroy($id);
        return response()->json(null, 204);
    }
}
