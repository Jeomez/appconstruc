<?php

namespace App\Http\Controllers;

use App\Models\obra;
use Illuminate\Http\Request;

class ObraController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index() {
        return Obra::all();
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
        $obra = Obra::create($request->all());
        return response()->json($obra, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id) {
        return Obra::findOrFail($id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(obra $obra)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {
        $obra = Obra::findOrFail($id);
        $obra->update($request->all());
        return response()->json($obra, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {
        Obra::destroy($id);
        return response()->json(null, 204);
    }
}
