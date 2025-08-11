<?php

namespace App\Http\Controllers;

use App\Models\Combustible;
use Illuminate\Http\Request;

class CombustibleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index() {
        return Combustible::all();
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
        $combustible = Combustible::create($request->all());
        return response()->json($combustible, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id) {
        return Combustible::findOrFail($id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Combustible $combustible)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {
        $combustible = Combustible::findOrFail($id);
        $combustible->update($request->all());
        return response()->json($combustible, 200);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {
        Combustible::destroy($id);
        return response()->json(null, 204);
    }
}
