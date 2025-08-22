<?php

namespace App\Http\Controllers;

use App\Models\operador;
use Illuminate\Http\Request;

class OperadorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index() {
        return Operador::all();
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
        $operador = Operador::create($request->all());
        return response()->json($operador, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id) {
        return Operador::findOrFail($id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(operador $operador)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {
        $operador = Operador::findOrFail($id);
        $operador->update($request->all());
        return response()->json($operador, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {
        Operador::destroy($id);
        return response()->json(null, 204);
    }
}
