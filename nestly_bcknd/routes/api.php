<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PropiedadController;  
use App\Http\Controllers\Api\TipoPropiedadController;

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::get('/tipos-propiedad', [TipoPropiedadController::class, 'index']);
    
// Rutas protegidas
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    
    // Rutas de usuarios
    Route::get('users', [UserController::class, 'index']);
    Route::get('users/{id}', [UserController::class, 'show']);
    Route::put('users/{id}', [UserController::class, 'update']);
    Route::delete('users/{id}', [UserController::class, 'destroy']);
    Route::get('user', [AuthController::class, 'logData']);
    Route::post('user/profile-picture', [UserController::class, 'updateProfilePicture']);
    Route::delete('/users/{user}/avatar', [UserController::class, 'deleteProfilePicture']);
    // Rutas de propiedades
    Route::post('propiedades', [PropiedadController::class, 'store']);  
    Route::get('propiedades', [PropiedadController::class, 'index']);  
    Route::get('propiedades/{id}', [PropiedadController::class, 'show']);  
    Route::put('propiedades/{id}', [PropiedadController::class, 'update']);  
    Route::delete('propiedades/{id}', [PropiedadController::class, 'destroy']); 
    Route::post('update-profile-picture', [UserController::class, 'updateProfilePicture']); 
    

    Route::get('users/{user}/propiedades', [PropiedadController::class, 'indexByUser']);
});
    