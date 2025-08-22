<?php

namespace App\Http\Controllers;

use App\Models\unidad;
use Illuminate\Http\Request;

class UnidadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index() {
        return Unidad::all();
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
        $unidad = Unidad::create($request->all());
        return response()->json($unidad, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id) {
        return Unidad::findOrFail($id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(unidad $unidad)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {
        $unidad = Unidad::findOrFail($id);
        $unidad->update($request->all());
        return response()->json($unidad, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {
        Unidad::destroy($id);
        return response()->json(null, 204);
    }
}
