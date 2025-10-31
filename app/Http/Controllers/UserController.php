<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * GET /api/users
     */
    public function index()
    {
        // Igual que en EquipoController: todo el listado (sin resource/pagination).
        // Si luego quieres paginar, lo cambiamos.
        return response()->json(User::all());
    }

    /**
     * POST /api/users
     */
    public function store(Request $request)
    {
        Log::info('Payload recibido (users.store)', $request->all());

        // Validación directa (sin FormRequest)
        $data = $request->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['required','email','max:255','unique:users,email'],
            'password' => ['required','string','min:8'],
            'role'     => ['nullable','string','max:50'], // tienes la migración de role
        ]);

        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        // El modelo oculta password con $hidden; así no lo devuelves
        return response()->json($user, 201);
    }

    /**
     * GET /api/users/{id}
     */
    public function show($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }

    /**
     * PUT/PATCH /api/users/{id}
     */
    public function update(Request $request, $id)
    {
        $user  = User::findOrFail($id);

        // Validación directa; email único ignorando el propio ID
        $data = $request->validate([
            'name'     => ['sometimes','required','string','max:255'],
            'email'    => ['sometimes','required','email','max:255','unique:users,email,'.$user->id],
            'password' => ['nullable','string','min:6'],
            'role'     => ['nullable','string','max:50'],
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return response()->json($user, 200);
    }

    /**
     * DELETE /api/users/{id}
     */
    public function destroy($id)
    {
        User::destroy($id);
        return response()->json(null, 204);
    }
}
