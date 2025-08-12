<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PropiedadController;  
use App\Http\Controllers\Api\TipoPropiedadController;
use App\Http\Controllers\ResenaController;
use App\Http\Controllers\FavoritosController;
use App\Http\Controllers\Api\ResenaVotoController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\ReporteController;

// Rutas públicas (sin autenticación)
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::get('/tipos-propiedad', [TipoPropiedadController::class, 'index']);
Route::get('propiedades', [PropiedadController::class, 'index']);  
Route::get('propiedades/{id}', [PropiedadController::class, 'show']);  

// Rutas para usuarios autenticados (cualquier rol)
Route::middleware('auth:sanctum', 'check.user.status')->group(function () {

    // Logout
    Route::post('logout', [AuthController::class, 'logout']);

    // Perfil propio (ver, editar, avatar)
    Route::get('user', [AuthController::class, 'logData']); 
    Route::put('user', [UserController::class, 'updateOwnProfile']);
    Route::delete('user', [UserController::class, 'deleteOwnProfile']);
    Route::post('user/avatar', [UserController::class, 'updateOwnAvatar']);
    Route::delete('user/avatar', [UserController::class, 'deleteOwnAvatar']);

    // Propiedades (para cualquier usuario autenticado)
    Route::post('propiedades', [PropiedadController::class, 'store']);  
    Route::put('propiedades/{id}', [PropiedadController::class, 'update']);  
    Route::delete('propiedades/{id}', [PropiedadController::class, 'destroy']); 
    Route::get('users/{user}/propiedades', [PropiedadController::class, 'indexByUser']);
    Route::put('/propiedades/{propiedad}/estado', [PropiedadController::class, 'actualizarEstado']);
    Route::get('propiedades/rentadas/mias', [PropiedadController::class, 'obtenerRentadasPorUsuario']);

    // Reseñas
    Route::get('propiedades/{propiedad}/resenas', [ResenaController::class, 'index']);
    Route::post('propiedades/{propiedad}/resenas', [ResenaController::class, 'store']);
    Route::get('/resenas/liked-ids', [ResenaController::class, 'getLikedIds']);
    Route::get('resenas/{resena}', [ResenaController::class, 'show']);
    Route::put('resenas/{resena}', [ResenaController::class, 'update']);
    Route::delete('resenas/{resena}', [ResenaController::class, 'destroy']);
    Route::post('resenas/{resena}/voto', [ResenaVotoController::class, 'toggle']);

    // Favoritos
    Route::get('/favoritos', [FavoritosController::class, 'index']);
    Route::get('/favoritos/ids', [FavoritosController::class, 'indexIds']);
    Route::post('/favoritos/agregar/{propiedadId}', [FavoritosController::class, 'store']);
    Route::delete('/favoritos/quitar/{propiedadId}', [FavoritosController::class, 'destroy']);

    // Reportes
    Route::post('/reportes', [ReporteController::class, 'store']);
    
});

// Rutas exclusivas para admins
Route::middleware(['auth:sanctum', 'check.role:admin'])->group(function () {
    // Gestión completa de usuarios
    Route::get('admin/stats', [AdminDashboardController::class, 'getDashboardData']);
    Route::put('users/{user}/estado', [UserController::class, 'cambiarEstadoUsuario']);
    Route::get('users', [UserController::class, 'index']);
    Route::get('users/{id}', [UserController::class, 'show']);
    Route::put('users/{id}', [UserController::class, 'update']);
    Route::delete('users/{id}', [UserController::class, 'destroy']);
    
    Route::get('/reportes', [ReporteController::class, 'index']);
    Route::put('/reportes/{reporte}/estado', [ReporteController::class, 'actualizarEstado']);
    Route::patch('/reportes/{reporte}/suspender-usuario', [ReporteController::class, 'suspenderUsuarioReportado']);
    Route::delete('/reportes/{reporte}/eliminar-propiedad', [ReporteController::class, 'eliminarPropiedadReportada']);
    Route::delete('/reportes/{reporte}/eliminar-resena', [ReporteController::class, 'eliminarResenaReportada']);
    
});
