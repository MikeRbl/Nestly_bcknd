<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PropiedadController;  
use App\Http\Controllers\Api\TipoPropiedadController;
use App\Http\Controllers\ResenaController;
use App\Http\Controllers\Api\ResenaVotoController;

// Rutas públicas (sin autenticación)
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::get('/tipos-propiedad', [TipoPropiedadController::class, 'index']);

// Rutas para usuarios autenticados (cualquier rol)
Route::middleware('auth:sanctum')->group(function () {

    // Logout
    Route::post('logout', [AuthController::class, 'logout']);

    // Perfil propio (ver, editar, avatar)
    Route::get('user', [AuthController::class, 'logData']); 
    Route::put('user', [UserController::class, 'updateOwnProfile']);
    Route::delete('user', [UserController::class, 'deleteOwnProfile']);
    
    Route::post('user/avatar', [UserController::class, 'updateOwnAvatar']);
    // Borrar avatar propio
    Route::delete('user/avatar', [UserController::class, 'deleteOwnAvatar']);
    // Propiedades (para cualquier usuario autenticado)
    Route::post('propiedades', [PropiedadController::class, 'store']);  
    Route::get('propiedades', [PropiedadController::class, 'index']);  
    Route::get('propiedades/{id}', [PropiedadController::class, 'show']);  
    Route::put('propiedades/{id}', [PropiedadController::class, 'update']);  
    Route::delete('propiedades/{id}', [PropiedadController::class, 'destroy']); 
    Route::get('users/{user}/propiedades', [PropiedadController::class, 'indexByUser']);

    // Reseñas
    Route::get('propiedades/{propiedad}/resenas', [ResenaController::class, 'index']);
    Route::post('propiedades/{propiedad}/resenas', [ResenaController::class, 'store']);
    Route::get('resenas/{resena}', [ResenaController::class, 'show']);
    Route::put('resenas/{resena}', [ResenaController::class, 'update']);
    Route::delete('resenas/{resena}', [ResenaController::class, 'destroy']);
    Route::post('resenas/{resena}/voto', [ResenaVotoController::class, 'toggle']);
});

// Rutas exclusivas para admins
Route::middleware(['auth:sanctum', 'check.role:admin'])->group(function () {
    // Gestión completa de usuarios
    Route::get('users', [UserController::class, 'index']);
    Route::get('users/{id}', [UserController::class, 'show']);
    Route::put('users/{id}', [UserController::class, 'update']);
    Route::delete('users/{id}', [UserController::class, 'destroy']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);
});
