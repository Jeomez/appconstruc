<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

use App\Models\User;

use App\Http\Controllers\CombustibleController;
use App\Http\Controllers\TipoEquipoController;
use App\Http\Controllers\EquipoController;
use App\Http\Controllers\CargaController;
use App\Http\Controllers\OperadorController;
use App\Http\Controllers\UnidadController;
use App\Http\Controllers\ObraController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReporteRendimientoController;

/*
|--------------------------------------------------------------------------
| Rutas PÚBLICAS
|--------------------------------------------------------------------------
*/

// Registro (opcional)
Route::post('/register', function (Request $request) {
    $data = $request->validate([
        'name'     => 'required|string|max:255',
        'email'    => 'required|email|unique:users,email',
        'password' => 'required|min:6',
    ]);

    $user = User::create([
        'name'     => $data['name'],
        'email'    => $data['email'],
        'password' => Hash::make($data['password']),
    ]);

    return response()->json(['message' => 'Usuario registrado'], 201);
});

// Login (deja SOLO UNA definición)
Route::post('/login', function (Request $request) {
    $request->validate([
        'email'    => 'required|email',
        'password' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Credenciales inválidas'], 401);
    }

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'message' => 'Login exitoso',
        'token'   => $token,
        'user'    => $user, // si no quieres devolver el user, elimínalo
        
    ]);
});

// Ping
Route::get('/ping', fn () => response()->json(['pong' => true]));

// Subida de foto (pública como la tenías; si debe ser privada, muévela al grupo protegido)
Route::post('/upload-foto', function (Request $request) {
    if ($request->hasFile('foto')) {
        $path = $request->file('foto')->store('fotos', 'public');
        $url  = Storage::url($path);
        return response()->json(['ruta' => $url]);
    }
    return response()->json(['error' => 'No se recibió archivo'], 400);
});

/*
|--------------------------------------------------------------------------
| Rutas PROTEGIDAS (auth:sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // Cerrar sesión (revoca el token actual)
    Route::post('/logout', function (Request $request) {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Sesión cerrada']);
    });

    // Usuario autenticado
    Route::get('/user', fn (Request $request) => $request->user());

    // Users CRUD
    Route::apiResource('users', UserController::class);

    // Combustibles
    Route::apiResource('combustibles', CombustibleController::class);

    // Tipo de equipos
    Route::apiResource('tipo_equipos', TipoEquipoController::class);

    // Equipos
    Route::apiResource('equipos', EquipoController::class);
    Route::get('/equipos/numeco/{numeco}', [EquipoController::class, 'showByNumeco']);

    // Cargas (dejaste apiResource comentado; mantengo las rutas manuales)
    Route::get('/cargas/next-folio', [CargaController::class, 'nextFolio']);
    Route::get('/cargas', [CargaController::class, 'index']);
    Route::post('/cargas', [CargaController::class, 'store']);
    Route::get('/cargas/{id}', [CargaController::class, 'show'])->whereNumber('id');
    Route::put('/cargas/{id}', [CargaController::class, 'update'])->whereNumber('id');
    Route::delete('/cargas/{id}', [CargaController::class, 'destroy'])->whereNumber('id');

    // Operadores
    Route::apiResource('operadors', OperadorController::class);

    // Unidades
    Route::apiResource('unidads', UnidadController::class);

    // Obras
    Route::apiResource('obras', ObraController::class);

    Route::get('equipos/{equipo}/rendimiento', [ReporteRendimientoController::class, 'show']);
});
