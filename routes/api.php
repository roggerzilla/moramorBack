<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;
use App\Http\Middleware\RoleMiddleware; // Importa el middleware RoleMiddleware

// ============================================
// ✅ RUTAS PÚBLICAS (CUALQUIER USUARIO)
// ============================================
Route::post('/registerAdmin', [AuthController::class, 'registerAdmin']);
Route::post('/register', [AuthController::class, 'register']); // Registro de usuarios
Route::post('/login', [AuthController::class, 'login']); // Inicio de sesión

// Rutas de verificación de correo electrónico
Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
    ->name('verification.verify');

Route::post('/email/resend', [AuthController::class, 'resendVerificationEmail'])
    ->middleware(['auth:sanctum', 'throttle:6,1'])
    ->name('verification.resend');

// ============================================
// ✅ RUTAS PARA EL CARRITO (SOLO USUARIOS LOGUEADOS)
// ============================================
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/cart', [CartController::class, 'getCartItems']); // Obtener ítems del carrito
    Route::post('/cart/add', [CartController::class, 'addToCart']); // Agregar un producto al carrito
    Route::put('/cart/update/{id}', [CartController::class, 'updateCartItem']); // Actualizar un ítem del carrito
    Route::delete('/cart/remove/{id}', [CartController::class, 'removeFromCart']); // Eliminar un ítem del carrito
});

// ============================================
// ✅ RUTAS PARA INVENTARIO (SUPER USUARIO Y ADMIN)
// ============================================
Route::middleware(['auth:sanctum', RoleMiddleware::class . ':superuser,admin'])->group(function () {
    Route::get('/items', [InventoryController::class, 'getItems']); // Obtener productos
    Route::get('/items/{id}', [InventoryController::class, 'getItem']); // Obtener producto específico
});

// ============================================
// ✅ RUTAS PARA MODIFICAR INVENTARIO (SOLO SUPER USUARIO)
// ============================================
Route::middleware(['auth:sanctum', RoleMiddleware::class . ':superuser'])->group(function () {
    Route::post('/items', [InventoryController::class, 'addItem']); // Agregar producto
    Route::put('/items/{id}', [InventoryController::class, 'updateItem']); // Actualizar producto
    Route::delete('/items/{id}', [InventoryController::class, 'deleteItem']); // Eliminar producto
    Route::post('/upload-image', [InventoryController::class, 'uploadImage']); // Subir imagen de producto
});

// ============================================
// ✅ RUTAS PARA ADMINISTRACIÓN DE USUARIOS
// ============================================
// ✅ SOLO EL SUPER USUARIO PUEDE CREAR ADMINISTRADORES
Route::middleware(['auth:sanctum', RoleMiddleware::class . ':superuser'])->group(function () {
    Route::post('/asignar-admin', [UserController::class, 'assignAdmin']); // Asignar rol de administrador
});

// ============================================
// ✅ RUTAS PARA MANEJO DE SESIÓN (LOGIN / LOGOUT)
// ============================================
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user(); // Obtener usuario autenticado
    });

    Route::post('/logout', [AuthController::class, 'logout']); // Cerrar sesión
});

// ============================================
// ✅ RUTA PARA REGISTRAR UBICACIÓN (OPCIONAL)
// ============================================
Route::post('/location', function (Request $request) {
    $latitude = $request->input('latitude');
    $longitude = $request->input('longitude');

    // Aquí puedes almacenar la ubicación si lo deseas
    return response()->json(['message' => 'Location received'], 200);
});

// ============================================
// ✅ RUTAS PARA REGISTRAR USUARIOS Y OBTENER USUARIOS (SOLO SUPER USUARIO)
// ============================================
Route::middleware(['auth:sanctum', RoleMiddleware::class . ':superuser'])->group(function () {
    Route::post('/register-user', [UserController::class, 'registerUser']);
    Route::get('/users', [UserController::class, 'getUsers']);
    Route::get('/admins', [UserController::class, 'getAdmins']);
});


Route::middleware(['auth:sanctum', RoleMiddleware::class . ':superuser'])->group(function () {
    Route::delete('/admins/{id}', [UserController::class, 'deleteAdmin']); // Eliminar un admin
    Route::post('/admins/{id}/restore', [UserController::class, 'restoreAdmin']); // Restaurar un admin
    Route::get('/admins/deleted', [UserController::class, 'getDeletedAdmins']); // Obtener admins eliminados
    Route::put('/admins/{id}', [UserController::class, 'updateAdmin']);// Actualizar un admin
});


Route::middleware(['auth:sanctum',RoleMiddleware::class . ':superuser,admin'])->group(function () {
    Route::get('/orders', [OrderController::class, 'index']);
});

Route::post('/ordersStore', [OrderController::class, 'store']);

Route::middleware(['auth:sanctum', RoleMiddleware::class . ':superuser,admin'])->get('/user', [UserController::class, 'getUserInfo']);

Route::middleware('auth:sanctum')->post('/cart/clear', [CartController::class, 'clearCart']);