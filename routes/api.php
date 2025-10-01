<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CombustibleController;
use App\Http\Controllers\TipoEquipoController;
use App\Http\Controllers\EquipoController;
use App\Http\Controllers\CargaController;
use App\Http\Controllers\OperadorController;
use App\Http\Controllers\UnidadController;
use App\Http\Controllers\ObraController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

Route::middleware('auth:sanctum')->get('/api/user', fn() => request()->user());

Route::post('/register', function (Request $request) {
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:6',
    ]);

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
    ]);

    return response()->json(['message' => 'Usuario registrado'], 201);
});


Route::post('/login', function (Request $request) {
    $user = User::where('email', $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Credenciales inválidas'], 401);
    }

    return ['token' => $user->createToken('token-name')->plainTextToken];
});

// Login
Route::post('/login', function (Request $request) {
    $user = User::where('email', $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Credenciales inválidas'], 401);
    }

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'message' => 'Login exitoso',
        'token' => $token,
        'user' => $user,
    ]);
});

// Logout (revoca el token actual)
Route::middleware('auth:sanctum')->post('/logout', function (Request $request) {
    $request->user()->currentAccessToken()->delete();

    return response()->json(['message' => 'Sesión cerrada']);
});

// Ruta protegida (requiere token)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::apiResource('combustibles', CombustibleController::class);
Route::apiResource('tipo_equipos', TipoEquipoController::class);
Route::apiResource('equipos', EquipoController::class);
//Route::apiResource('cargas', CargaController::class);
Route::apiResource('operadors', OperadorController::class);
Route::apiResource('unidads', UnidadController::class);
Route::apiResource('obras', ObraController::class);


Route::get('/ping', function () {
    return response()->json(['pong' => true]);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/combustibles', [CombustibleController::class, 'index']);
    Route::post('/combustibles', [CombustibleController::class, 'store']);
    Route::get('/combustibles/{id}', [CombustibleController::class, 'show']);
    Route::put('/combustibles/{id}', [CombustibleController::class, 'update']);
    Route::delete('/combustibles/{id}', [CombustibleController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/tipo_equipos', [TipoEquipoController::class, 'index']);
    Route::post('/tipo_equipos', [TipoEquipoController::class, 'store']);
    Route::get('/tipo_equipos/{id}', [TipoEquipoController::class, 'show']);
    Route::put('/tipo_equipos/{id}', [TipoEquipoController::class, 'update']);
    Route::delete('/tipo_equipos/{id}', [TipoEquipoController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/equipos', [EquipoController::class, 'index']);
    Route::post('/equipos', [EquipoController::class, 'store']);
    Route::get('/equipos/{id}', [EquipoController::class, 'show']);
    Route::put('/equipos/{id}', [EquipoController::class, 'update']);
    Route::delete('/equipos/{id}', [EquipoController::class, 'destroy']);
    Route::get('/equipos/numeco/{numeco}', [EquipoController::class, 'showByNumeco']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/cargas/next-folio', [CargaController::class, 'nextFolio']);
    Route::get('/cargas', [CargaController::class, 'index']);
    Route::post('/cargas', [CargaController::class, 'store']);
    Route::get('/cargas/{id}', [CargaController::class, 'show'])->whereNumber('id');
    Route::put('/cargas/{id}', [CargaController::class, 'update'])->whereNumber('id');
    Route::delete('/cargas/{id}', [CargaController::class, 'destroy'])->whereNumber('id');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/operadors', [OperadorController::class, 'index']);
    Route::post('/operadors', [OperadorController::class, 'store']);
    Route::get('/operadors/{id}', [OperadorController::class, 'show']);
    Route::put('/operadors/{id}', [OperadorController::class, 'update']);
    Route::delete('/operadors/{id}', [OperadorController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/unidads', [UnidadController::class, 'index']);
    Route::post('/unidads', [UnidadController::class, 'store']);
    Route::get('/unidads/{id}', [UnidadController::class, 'show']);
    Route::put('/unidads/{id}', [UnidadController::class, 'update']);
    Route::delete('/unidads/{id}', [UnidadController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/obras', [ObraController::class, 'index']);
    Route::post('/obras', [ObraController::class, 'store']);
    Route::get('/obras/{id}', [ObraController::class, 'show']);
    Route::put('/obras/{id}', [ObraController::class, 'update']);
    Route::delete('/obras/{id}', [ObraController::class, 'destroy']);
});

Route::post('/upload-foto', function (\Illuminate\Http\Request $request) {
    if ($request->hasFile('foto')) {
        // ✅ Guarda correctamente en storage/app/public/fotos
        $path = $request->file('foto')->store('fotos', 'public');

        // ✅ Devuelve la ruta pública
        $url = \Illuminate\Support\Facades\Storage::url($path);
        return response()->json(['ruta' => $url]);
    }

    return response()->json(['error' => 'No se recibió archivo'], 400);
});

// routes/api.php







