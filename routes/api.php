<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Controllers\NotifyController;
use App\Http\Controllers\StripeController;

// ============================================
// ✅ RUTAS PÚBLICAS (CUALQUIER USUARIO)
// ============================================
Route::post('/registerAdmin', [AuthController::class, 'registerAdmin']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Rutas de verificación de correo electrónico

Route::post('/email/resend', [AuthController::class, 'resendVerificationEmail'])
    ->middleware(['auth:sanctum', 'throttle:6,1'])
    ->name('verification.resend');

// ============================================
// ✅ RUTAS PARA EL CARRITO (SOLO USUARIOS LOGUEADOS)
// ============================================
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/cart', [CartController::class, 'getCartItems']);
    Route::post('/cart/add', [CartController::class, 'addToCart']);
    Route::put('/cart/update/{id}', [CartController::class, 'updateCartItem']);
    Route::delete('/cart/remove/{id}', [CartController::class, 'removeFromCart']);
    Route::post('/cart/clear', [CartController::class, 'clearCart']);
});

// ============================================
// ✅ RUTAS PARA INVENTARIO (SUPER USUARIO Y ADMIN)
// ============================================
Route::get('/items', [InventoryController::class, 'getItems']);
Route::get('/items/{id}', [InventoryController::class, 'getItem']);

// ============================================
// ✅ RUTAS PARA MODIFICAR INVENTARIO (SOLO SUPER USUARIO)
// ============================================
Route::middleware(['auth:sanctum', RoleMiddleware::class . ':superuser'])->group(function () {
    Route::post('/items', [InventoryController::class, 'addItem']);
    Route::put('/items/{id}', [InventoryController::class, 'updateItem']);
    Route::delete('/items/{id}', [InventoryController::class, 'deleteItem']);
    Route::post('/upload-image', [InventoryController::class, 'uploadImage']);
});

// ============================================
// ✅ RUTAS PARA ADMINISTRACIÓN DE USUARIOS
// ============================================
Route::middleware(['auth:sanctum', RoleMiddleware::class . ':superuser'])->group(function () {
    Route::post('/asignar-admin', [UserController::class, 'assignAdmin']);
    Route::post('/register-user', [UserController::class, 'registerUser']);
    Route::get('/users', [UserController::class, 'getUsers']);
    Route::get('/admins', [UserController::class, 'getAdmins']);
    Route::delete('/admins/{id}', [UserController::class, 'deleteAdmin']);
    Route::post('/admins/{id}/restore', [UserController::class, 'restoreAdmin']);
    Route::get('/admins/deleted', [UserController::class, 'getDeletedAdmins']);
    Route::put('/admins/{id}', [UserController::class, 'updateAdmin']);
});

// ============================================
// ✅ RUTAS PARA MANEJO DE SESIÓN (LOGIN / LOGOUT)
// ============================================
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);
});

// ============================================
// ✅ RUTAS PARA ÓRDENES
// ============================================
Route::middleware(['auth:sanctum', RoleMiddleware::class . ':superuser,admin'])->group(function () {
    Route::get('/orders', [OrderController::class, 'index']);
    Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus']);
});

Route::post('/ordersStore', [OrderController::class, 'store']);

Route::post('/create-payment-intent', [StripeController::class, 'createPaymentIntent']);
//notificaciones stock
    Route::middleware('auth:sanctum')->post('/stock-alerts', [NotifyController::class, 'store']);


