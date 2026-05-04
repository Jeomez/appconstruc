<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\NumeroEconomicoService;
use App\Services\EquipoArchivoService;


class EquipoController extends Controller
{
    /**
     * Helper: si el scope es assigned_equipment, el equipo debe estar asignado (active=1) al usuario.
     */
    private function authorizeEquipoByScope(Request $request, Equipo $equipo, string $permissionKey = 'equipo.create_update'): void
    {
        $scope = $request->user()->getPermissionScope($permissionKey);

        if ($scope !== 'assigned_equipment') {
            return; // all o null => sin restricción por asignación
        }

        $userId = $request->user()->id;

        $isAssigned = DB::table('equipo_user')
            ->where('equipo_id', $equipo->id)
            ->where('user_id', $userId)
            ->where('active', 1)
            ->exists();

        if (!$isAssigned) {
            abort(403, 'Forbidden');
        }
    }

    public function showByNumeco(Request $request, string $numeco)
    {
        $equipo = Equipo::where('numeco', $numeco)->first();

        if (!$equipo) {
            return response()->json(['message' => 'Equipo no encontrado'], 404);
        }

        $this->authorizeEquipoByScope($request, $equipo, 'equipo.create_update');

        $combustible = null;

        if ($equipo->combustible) {
            $combustible = DB::table('combustibles')
                ->where('id', $equipo->combustible)
                ->first();
        }

        return response()->json([
            'id'                => $equipo->id,
            'desc_equipo'       => $equipo->descripcion ?? $equipo->nombre ?? '',
            'id_combustible'    => $equipo->combustible ?? null,
            'desc_combustible'  => $combustible->nombre ?? '',
            'noserie'           => $equipo->noserie ?? null,
            'peso'              => $equipo->peso ?? null,
            'libras'            => $equipo->libras ?? null,
            'apodo'             => $equipo->apodo ?? '',
        ]);
    }

    public function index(Request $request)
    {
        $query = Equipo::query();

        $scope = $request->user()->getPermissionScope('equipo.create_update');

        if ($scope === 'assigned_equipment') {
            // Requiere relación users() en Equipo.php
            $userId = $request->user()->id;

            $query->whereHas('users', function ($q) use ($userId) {
                $q->where('users.id', $userId)
                    ->where('equipo_user.active', 1);
            });
        }

        return response()->json($query->get());
    }

    public function store(
        Request $request,
        NumeroEconomicoService $numecoSvc,
        EquipoArchivoService $equipoArchivoService
    ) {
        Log::info('Payload recibido (equipos.store)', $request->all());

        $data = $request->validate([
            'numeco'            => ['nullable', 'string', 'max:255'],

            'nombre'            => ['required', 'string', 'max:255'],
            'tipo'              => ['required', 'integer', 'in:1,2,3,4'],
            'clave'             => ['required', 'string', 'max:4'],
            'estado'            => ['required', 'string', 'max:255'],

            'placas'            => ['nullable', 'string', 'max:255'],
            'vigenciaplacas'    => ['nullable', 'date'],
            'poliza'            => ['nullable', 'string', 'max:255'],
            'vigenciapoliza'    => ['nullable', 'date'],
            'noserie'           => ['nullable', 'string', 'max:255'],
            'ulthorometro'      => ['nullable', 'integer', 'min:0'],
            'combustible'       => ['nullable', 'integer'],
            'responsable'       => ['nullable', 'string', 'max:255'],
            'operador'          => ['nullable', 'string', 'max:255'],
            'foto'              => ['nullable', 'string', 'max:255'],
            'obra'              => ['nullable', 'integer'],
            'rango_inferior'    => ['nullable', 'integer'],
            'rango_superior'    => ['nullable', 'integer'],
            'unidad_rend'       => ['nullable', 'integer'],
            'id_operador'       => ['nullable', 'integer'],
            'peso'              => ['nullable', 'numeric'],
            'largo'             => ['nullable', 'numeric'],
            'alto'              => ['nullable', 'numeric'],
            'ancho'             => ['nullable', 'numeric'],
            'apodo'             => ['nullable', 'string', 'max:255'],
            'rango_inferior2'   => ['nullable', 'integer'],
            'rango_superior2'   => ['nullable', 'integer'],
            'unidad_rend2'      => ['nullable', 'integer'],
            'rango_inferior3'   => ['nullable', 'integer'],
            'rango_superior3'   => ['nullable', 'integer'],
            'unidad_rend3'      => ['nullable', 'integer'],
            'libras'            => ['sometimes', 'nullable', 'numeric'],

            // 👇 NUEVO
            'upload_token'      => ['nullable', 'uuid'],
        ]);

        $data['clave'] = strtoupper(trim($data['clave'] ?? ''));
        $data['clave'] = preg_replace('/\s+/', '', $data['clave']);
        $data['clave'] = substr($data['clave'], 0, 4);

        $data['numeco'] = $numecoSvc->generarPorTipoYClave(
            (int) $data['tipo'],
            (string) $data['clave']
        );

        $uploadToken = $data['upload_token'] ?? null;
        unset($data['upload_token']);

        Log::info('Upload token recibido en store', [
            'upload_token' => $uploadToken,
        ]);

        $equipo = DB::transaction(function () use ($data, $uploadToken, $equipoArchivoService) {
            $equipo = Equipo::create($data);

            Log::info('Equipo creado', [
                'equipo_id' => $equipo->id,
                'upload_token' => $uploadToken,
            ]);

            if (!empty($uploadToken)) {
                $equipoArchivoService->asociarTemporalesAEquipo($uploadToken, $equipo->id);
            } else {
                Log::warning('No vino upload_token al guardar equipo');
            }

            return $equipo;
        });

        return response()->json($equipo->load('archivos'), 201);
    }


    public function show(Request $request, $id)
    {
        $equipo = Equipo::findOrFail($id);

        // 🔐 scope
        $this->authorizeEquipoByScope($request, $equipo, 'equipo.create_update');

        return response()->json($equipo);
    }

    public function update(Request $request, $id)
    {
        $equipo = Equipo::findOrFail($id);

        // 🔐 scope (ya lo tenías)
        $this->authorizeEquipoByScope($request, $equipo, 'equipo.create_update');

        $data = $request->validate([
            'numeco'           => ['sometimes', 'string', 'max:255'],
            'nombre'           => ['sometimes', 'string', 'max:255'],
            'tipo'             => ['sometimes', 'integer'],
            'estado'           => ['sometimes', 'string', 'max:255'],

            'placas'           => ['sometimes', 'nullable', 'string', 'max:255'],
            'vigenciaplacas'   => ['sometimes', 'nullable', 'date'],
            'poliza'           => ['sometimes', 'nullable', 'string', 'max:255'],
            'vigenciapoliza'   => ['sometimes', 'nullable', 'date'],
            'noserie'          => ['sometimes', 'nullable', 'string', 'max:255'],
            'ulthorometro'     => ['sometimes', 'nullable', 'integer', 'min:0'],
            'combustible'      => ['sometimes', 'nullable', 'integer'],
            'responsable'      => ['sometimes', 'nullable', 'string', 'max:255'],
            'operador'         => ['sometimes', 'nullable', 'string', 'max:255'],
            'foto'             => ['sometimes', 'nullable', 'string', 'max:255'],
            'obra'             => ['sometimes', 'nullable', 'integer'],
            'rango_inferior'   => ['sometimes', 'nullable', 'integer'],
            'rango_superior'   => ['sometimes', 'nullable', 'integer'],
            'unidad_rend'      => ['sometimes', 'nullable', 'integer'],
            'id_operador'      => ['sometimes', 'nullable', 'integer'],
            'peso'             => ['sometimes', 'nullable', 'numeric'],
            'largo'             => ['nullable', 'numeric'],
            'alto'             => ['nullable', 'numeric'],
            'ancho'             => ['nullable', 'numeric'],
            'apodo'            => ['sometimes', 'nullable', 'string', 'max:255'],
            'rango_inferior2'   => ['nullable', 'integer'],
            'rango_superior2'   => ['nullable', 'integer'],
            'unidad_rend2'      => ['nullable', 'integer'],
            'rango_inferior3'   => ['nullable', 'integer'],
            'rango_superior3'   => ['nullable', 'integer'],
            'unidad_rend3'      => ['nullable', 'integer'],
            'libras'             => ['sometimes', 'nullable', 'numeric'],
        ]);

        $equipo->update($data);

        return response()->json($equipo, 200);
    }


    public function destroy(Request $request, $id)
    {
        $equipo = Equipo::findOrFail($id);

        // 🔐 Para borrar yo usaría permiso más fuerte:
        // si tienes equipo.deactivate úsalo aquí
        $this->authorizeEquipoByScope($request, $equipo, 'equipo.deactivate');

        $equipo->delete();

        return response()->json(['message' => 'Eliminado'], 200);
    }

    public function assign(Request $request, Equipo $equipo)
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $authUser = $request->user();

        // Seguridad: el que asigna debe poder editar equipos
        // (si ya lo proteges en routes con middleware permission, esto es extra)
        $this->authorizeEquipoByScope($request, $equipo, 'equipo.create_update');

        $targetUser = User::findOrFail($data['user_id']);

        // Seguridad: asignar solo dentro de la misma empresa
        if ($authUser->empresa_id && $targetUser->empresa_id && $authUser->empresa_id !== $targetUser->empresa_id) {
            return response()->json(['message' => 'No puedes asignar equipos a usuarios de otra empresa.'], 422);
        }

        DB::transaction(function () use ($equipo, $targetUser) {
            DB::table('equipo_user')
                ->where('equipo_id', $equipo->id)
                ->where('active', 1)
                ->update([
                    'active' => 0,
                    'updated_at' => now(),
                ]);

            // Evitar duplicar si ya existe una asignación igual activa (por si acaso)
            DB::table('equipo_user')->insert([
                'equipo_id' => $equipo->id,
                'user_id' => $targetUser->id,
                'active' => 1,
                'assigned_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return response()->json(['message' => 'Equipo asignado correctamente']);
    }
}
