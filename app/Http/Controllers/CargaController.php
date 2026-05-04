<?php

namespace App\Http\Controllers;

use App\Models\Carga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Equipo;
use Illuminate\Support\Facades\Auth;



class CargaController extends Controller
{
    public function nextFolio(Request $req)
    {
        $serie = $req->query('serie');
        abort_unless($serie, 422, 'serie requerida');

        $next = Carga::where('serie', $serie)->max('folio');
        return response()->json(['nextFolio' => (int)$next + 1]);
    }

    /**
     * GET /api/cargas
     * Filtros:
     *  - mes=YYYYMM (default: mes actual)
     *  - id_equipo, id_obra
     *  - sort: fecha_desc|fecha_asc|folio_desc|folio_asc (default: fecha_desc)
     *  - cursor (paginación por cursor), limit (default: 50)
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'mes'        => ['nullable', 'regex:/^[0-9]{6}$/'], // YYYYMM
            'id_equipo'  => ['nullable', 'integer', 'min:1'],
            'id_obra'    => ['nullable', 'integer', 'min:1'],
            'limit'      => ['nullable', 'integer', 'min:1', 'max:200'],
            'cursor'     => ['nullable', 'string'],
            'sort'       => ['nullable', 'in:fecha_desc,fecha_asc,folio_desc,folio_asc'],
        ]);

        //$mes       = $validated['mes'] ?? now()->format('Ym'); // default YYYYMM

        $id_equipo = $validated['id_equipo'] ?? null;
        $id_obra   = $validated['id_obra'] ?? null;
        $limit     = (int)($validated['limit'] ?? 50);
        $sort      = $validated['sort'] ?? 'fecha_desc';
        $cursor    = $validated['cursor'] ?? null;
        $mes = $validated['mes'] ?? null;

        if (!$mes && !$id_equipo && !$id_obra) {
            $mes = now()->format('Ym');
        }

        $query = Carga::query()
            ->select([
                'id_documento',
                'serie',
                'folio',
                'fecha',
                'mes',
                'hora',
                'id_equipo',
                'desc_equipo',
                'horometro',
                'id_combustible',
                'litros',
                'id_obra',
                'foto_ticket',
                'foto_horometro',
                'latitud',
                'longitud',
                'numeco',
                'created_at',
                'updated_at',
            ])
            ->when($mes, fn($q) => $q->where('mes', $mes))
            ->when($id_equipo, fn($q) => $q->where('id_equipo', $id_equipo))
            ->when($id_obra, fn($q) => $q->where('id_obra', $id_obra));

        // 🔐 Scope por permisos (assigned equipment)
        $scope = $request->user()->getPermissionScope('carga.view_history');
        if ($scope === 'assigned_equipment') {
            $query->whereHas('equipo.users', function ($q) use ($request) {
                $q->where('users.id', $request->user()->id)
                    ->where('equipo_user.active', 1);
            });
        }

        // Orden
        switch ($sort) {
            case 'fecha_asc':
                $query->orderBy('fecha')->orderBy('id_documento');
                break;
            case 'folio_asc':
                $query->orderBy('serie')->orderBy('folio');
                break;
            case 'folio_desc':
                $query->orderByDesc('serie')->orderByDesc('folio');
                break;
            default: // fecha_desc
                $query->orderByDesc('fecha')->orderByDesc('id_documento');
        }

        // Cursor paginate (acepta cursor opcional)
        $paginator = $query->cursorPaginate($limit, ['*'], 'cursor', $cursor)->withQueryString();

        return response()->json([
            'data' => $paginator->items(),
            'meta' => [
                'next_cursor' => $paginator->nextCursor()?->encode(),
                'prev_cursor' => $paginator->previousCursor()?->encode(),
                'count'       => count($paginator->items()),
                'limit'       => $limit,
                'mes'         => $mes,
                'sort'        => $sort,
                'id_equipo'   => $id_equipo,
                'id_obra'     => $id_obra,
            ],
        ]);
    }

    /**
     * POST /api/cargas
     * Acepta JSON o multipart/form-data, pero las fotos se envían como **rutas (string)**.
     * Ejemplo: { foto_ticket: "cargas/tickets/abc.jpg", foto_horometro: "cargas/horometros/xyz.jpg" }
     */


    public function store(Request $request)
    {
        $rules = [
            'serie'           => ['required', 'string', 'max:10'],
            'folio'           => ['required', 'integer', 'min:1'],
            'fecha'           => ['required', 'date'],
            'hora'            => ['nullable', 'date_format:H:i:s'],
            'id_equipo'       => ['required', 'integer', 'min:1'],
            'horometro'       => ['required', 'integer', 'min:0'],
            'id_combustible'  => ['required', 'integer', 'min:1'],
            'id_obra'         => ['required', 'integer', 'min:1'],
            'litros'          => ['required', 'numeric', 'min:0'],
            'latitud'         => ['nullable', 'numeric'],
            'longitud'        => ['nullable', 'numeric'],
            'mes'             => ['nullable', 'regex:/^[0-9]{6}$/'],
            'foto_ticket'     => ['nullable', 'string', 'max:255'],
            'foto_horometro'  => ['nullable', 'string', 'max:255'],
            'numeco'          => ['required', 'string'],
            'desc_equipo'     => ['nullable', 'string'],
        ];

        $data = $request->validate($rules);

        $equipo = Equipo::findOrFail($data['id_equipo']);

        // Solo calcular desc_equipo si no viene del front
        $data['desc_equipo'] = $data['desc_equipo']
            ?? $equipo->descripcion
            ?? $equipo->numeco
            ?? (string) $equipo->id;

        $data['mes'] = Carbon::parse($data['fecha'])->format('Ym');

        $user = Auth::user();
        $empresaId = $user->empresa_id;

        $exists = Carga::where('empresa_id', $empresaId)
            ->where('serie', $data['serie'])
            ->where('folio', $data['folio'])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Ya existe una carga con esa serie y folio en tu empresa.'
            ], 422);
        }

        $carga = Carga::create($data);

        return response()->json($carga, 201);
    }


    /**
     * GET /api/cargas/{id_documento}
     */
    public function show(Request $request, int $id)
    {
        $carga = Carga::find($id);
        if (!$carga) {
            return response()->json(['message' => 'No encontrada'], 404);
        }
        // 🔐 Para ver: carga.view_history
        $this->authorizeCargaByScope($request, $carga, 'carga.view_history');

        return response()->json($carga);
    }

    /**
     * PUT /api/cargas/{id_documento}
     * PATCH /api/cargas/{id_documento}
     * (Las fotos siguen siendo STRINGs/rutas)
     */
    public function update(Request $request, int $id)
    {
        $carga = Carga::find($id);
        if (!$carga) {
            return response()->json(['message' => 'No encontrada'], 404);
        }

        $this->authorizeCargaByScope($request, $carga, 'carga.update_cancel');

        $rules = [
            'serie'           => ['sometimes', 'string', 'max:10'],
            'folio'           => ['sometimes', 'integer', 'min:1'],
            'fecha'           => ['sometimes', 'date'],
            'hora'            => ['sometimes', 'nullable', 'date_format:H:i:s'],
            'id_equipo'       => ['sometimes', 'integer', 'min:1'],
            'desc_equipo'     => ['sometimes', 'string'],
            'horometro'       => ['sometimes', 'integer', 'min:0'],
            'id_combustible'  => ['sometimes', 'integer', 'min:1'],
            'litros'          => ['sometimes', 'numeric', 'min:0'],
            'id_obra'         => ['sometimes', 'integer', 'min:1'],

            'latitud'         => ['sometimes', 'nullable', 'numeric'],
            'longitud'        => ['sometimes', 'nullable', 'numeric'],

            'mes'             => ['nullable', 'regex:/^[0-9]{6}$/'], // se recalcula si mandan fecha

            // Fotos como STRING
            'foto_ticket'     => ['sometimes', 'nullable', 'string', 'max:255'],
            'foto_horometro'  => ['sometimes', 'nullable', 'string', 'max:255'],

            // (opcional) permitir actualizar numeco
            'numeco'          => ['sometimes', 'string'],
        ];

        $data = $request->validate($rules);

        if (array_key_exists('id_equipo', $data)) {
            $scope = $request->user()->getPermissionScope('carga.update_cancel');
            if ($scope === 'assigned_equipment') {
                $userId = $request->user()->id;

                $isAssigned = DB::table('equipo_user')
                    ->where('equipo_id', $data['id_equipo'])
                    ->where('user_id', $userId)
                    ->where('active', 1)
                    ->exists();

                if (!$isAssigned) {
                    return response()->json(['message' => 'No puedes asignar la carga a un equipo no asignado a ti.'], 403);
                }
            }
        }


        // Si mandan nueva fecha, recalculamos mes
        if (array_key_exists('fecha', $data)) {
            $data['mes'] = Carbon::parse($data['fecha'])->format('Ym');
        }

        // Si cambian serie/folio, validar unicidad
        if (array_key_exists('serie', $data) || array_key_exists('folio', $data)) {
            $serie = $data['serie'] ?? $carga->serie;
            $folio = $data['folio'] ?? $carga->folio;
            $exists = Carga::where('serie', $serie)
                ->where('folio', $folio)
                ->where('id_documento', '!=', $carga->id_documento)
                ->exists();
            if ($exists) {
                return response()->json(['message' => 'Ya existe una carga con esa serie y folio.'], 422);
            }
        }

        // Ojo: aquí NO manejamos archivos; solo guardamos la ruta que llegue (o null)
        $carga->update($data);

        return response()->json($carga);
    }

    /**
     * DELETE /api/cargas/{id_documento}
     */
    public function destroy(Request $request, int $id)
    {
        $carga = Carga::find($id);
        if (!$carga) {
            return response()->json(['message' => 'No encontrada'], 404);
        }

        $this->authorizeCargaByScope($request, $carga, 'carga.update_cancel');

        // (Opcional) borrar archivos asociados si tus rutas apuntan a storage/public
        if ($carga->foto_ticket) {
            Storage::disk('public')->delete($carga->foto_ticket);
        }
        if ($carga->foto_horometro) {
            Storage::disk('public')->delete($carga->foto_horometro);
        }

        $carga->delete();

        return response()->json(['message' => 'Eliminada']);
    }

    private function authorizeCargaByScope(Request $request, \App\Models\Carga $carga, string $permissionKey): void
    {
        $scope = $request->user()->getPermissionScope($permissionKey);

        if ($scope !== 'assigned_equipment') {
            return; // all o null => no restringe por equipo
        }

        $userId = $request->user()->id;

        $isAssigned = DB::table('equipo_user')
            ->where('equipo_id', $carga->id_equipo)
            ->where('user_id', $userId)
            ->where('active', 1)
            ->exists();

        if (!$isAssigned) {
            abort(403, 'Forbidden');
        }
    }
}
