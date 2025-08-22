<?php

namespace App\Http\Controllers;

use App\Models\Carga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class CargaController extends Controller
{
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
            'mes'        => ['nullable','regex:/^[0-9]{6}$/'],
            'id_equipo'  => ['nullable','integer','min:1'],
            'id_obra'    => ['nullable','integer','min:1'],
            'limit'      => ['nullable','integer','min:1','max:200'],
            'cursor'     => ['nullable','string'],
            'sort'       => ['nullable','in:fecha_desc,fecha_asc,folio_desc,folio_asc'],
        ]);

        $mes       = $validated['mes'] ?? now()->format('Ym');
        $id_equipo = $validated['id_equipo'] ?? null;
        $id_obra   = $validated['id_obra'] ?? null;
        $limit     = (int)($validated['limit'] ?? 50);
        $sort      = $validated['sort'] ?? 'fecha_desc';

        $query = Carga::query()
            ->select([
                'id_documento','serie','folio','fecha','mes','hora',
                'id_equipo','desc_equipo','horometro','id_combustible','litros','id_obra',
                'foto_ticket','foto_horometro','latitud','longitud',
                'created_at','updated_at',
            ])
            ->when($mes, fn($q) => $q->where('mes', $mes))
            ->when($id_equipo, fn($q) => $q->where('id_equipo', $id_equipo))
            ->when($id_obra, fn($q) => $q->where('id_obra', $id_obra));

        switch ($sort) {
            case 'fecha_asc':
                $query->orderBy('fecha')->orderBy('id_documento');
                break;
            case 'folio_asc':
                $query->orderBy('serie')->orderBy('folio');
                break;
            case 'folio_desc':
                $query->orderBy('serie','desc')->orderBy('folio','desc');
                break;
            default: // fecha_desc
                $query->orderByDesc('fecha')->orderByDesc('id_documento');
        }

        $paginator = $query->cursorPaginate($limit)->withQueryString();

        return response()->json([
            'data' => $paginator->items(),
            'meta' => [
                'next_cursor' => $paginator->nextCursor()?->encode(),
                'prev_cursor' => $paginator->previousCursor()?->encode(),
                'count'       => count($paginator->items()),
                'limit'       => $limit,
                'mes'         => $mes,
            ]
        ]);
    }

    /**
     * POST /api/cargas
     * Acepta JSON normal; para subir fotos usar multipart/form-data con
     * campos: foto_ticket (file), foto_horometro (file)
     */
    public function store(Request $request)
    {
        $rules = [
            'serie'           => ['required','string','max:10'],
            'folio'           => ['required','integer','min:1'],
            'fecha'           => ['required','date'], // "YYYY-MM-DD"
            'hora'            => ['nullable','date_format:H:i:s'],
            'id_equipo'       => ['required','integer','min:1'],
            'desc_equipo'     => ['required','string'],
            'horometro'       => ['required','integer','min:0'],
            'id_combustible'  => ['required','integer','min:1'],
            'litros'          => ['required','numeric','min:0'], // ej. 123.456
            'id_obra'         => ['required','integer','min:1'],

            'latitud'         => ['nullable','numeric'],
            'longitud'        => ['nullable','numeric'],

            // Opcional: si te mandan mes en el payload, lo validamos (pero lo recalculamos)
            'mes'             => ['nullable','regex:/^[0-9]{6}$/'],

            // Archivos
            'foto_ticket'     => ['nullable','file','image','max:4096'],    // ~4MB
            'foto_horometro'  => ['nullable','file','image','max:4096'],
        ];

        $data = $request->validate($rules);

        // Garantizar mes desde fecha (fallback si el modelo ya no lo hace)
        $data['mes'] = Carbon::parse($data['fecha'])->format('Ym');

        // Manejo de archivos (si vienen)
        if ($request->hasFile('foto_ticket')) {
            $data['foto_ticket'] = $request->file('foto_ticket')->store('cargas/tickets', 'public');
        }
        if ($request->hasFile('foto_horometro')) {
            $data['foto_horometro'] = $request->file('foto_horometro')->store('cargas/horometros', 'public');
        }

        // Unicidad serie-folio
        $exists = Carga::where('serie', $data['serie'])
            ->where('folio', $data['folio'])
            ->exists();
        if ($exists) {
            return response()->json(['message' => 'Ya existe una carga con esa serie y folio.'], 422);
        }

        $carga = Carga::create($data);

        return response()->json($carga, 201);
    }

    /**
     * GET /api/cargas/{id_documento}
     */
    public function show(int $id)
    {
        $carga = Carga::find($id);
        if (!$carga) {
            return response()->json(['message' => 'No encontrada'], 404);
        }
        return response()->json($carga);
    }

    /**
     * PUT /api/cargas/{id_documento}
     * PATCH /api/cargas/{id_documento}
     */
    public function update(Request $request, int $id)
    {
        $carga = Carga::find($id);
        if (!$carga) {
            return response()->json(['message' => 'No encontrada'], 404);
        }

        $rules = [
            'serie'           => ['sometimes','string','max:10'],
            'folio'           => ['sometimes','integer','min:1'],
            'fecha'           => ['sometimes','date'],
            'hora'            => ['sometimes','nullable','date_format:H:i:s'],
            'id_equipo'       => ['sometimes','integer','min:1'],
            'desc_equipo'     => ['sometimes','string'],
            'horometro'       => ['sometimes','integer','min:0'],
            'id_combustible'  => ['sometimes','integer','min:1'],
            'litros'          => ['sometimes','numeric','min:0'],
            'id_obra'         => ['sometimes','integer','min:1'],

            'latitud'         => ['sometimes','nullable','numeric'],
            'longitud'        => ['sometimes','nullable','numeric'],

            'mes'             => ['nullable','regex:/^[0-9]{6}$/'], // se recalcula si mandan fecha

            // Archivos
            'foto_ticket'     => ['sometimes','nullable','file','image','max:4096'],
            'foto_horometro'  => ['sometimes','nullable','file','image','max:4096'],
        ];

        $data = $request->validate($rules);

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

        // Manejo de archivos (reemplazar si llegan nuevos)
        if ($request->hasFile('foto_ticket')) {
            // opcional: borrar anterior
            if ($carga->foto_ticket) {
                Storage::disk('public')->delete($carga->foto_ticket);
            }
            $data['foto_ticket'] = $request->file('foto_ticket')->store('cargas/tickets', 'public');
        }
        if ($request->hasFile('foto_horometro')) {
            if ($carga->foto_horometro) {
                Storage::disk('public')->delete($carga->foto_horometro);
            }
            $data['foto_horometro'] = $request->file('foto_horometro')->store('cargas/horometros', 'public');
        }

        $carga->update($data);

        return response()->json($carga);
    }

    /**
     * DELETE /api/cargas/{id_documento}
     */
    public function destroy(int $id)
    {
        $carga = Carga::find($id);
        if (!$carga) {
            return response()->json(['message' => 'No encontrada'], 404);
        }

        // (Opcional) borrar archivos asociados
        if ($carga->foto_ticket) {
            Storage::disk('public')->delete($carga->foto_ticket);
        }
        if ($carga->foto_horometro) {
            Storage::disk('public')->delete($carga->foto_horometro);
        }

        $carga->delete();

        return response()->json(['message' => 'Eliminada']);
    }
}
