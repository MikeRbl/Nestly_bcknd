<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // Obtener todos los usuarios (solo admin)
    public function index()
    {
        $users = User::all();
        return response()->json($users);
    }

    // Mostrar un usuario específico
    public function show($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }

    // Actualizar un usuario (ej: perfil)
    public function update(Request $request, $id)
    {
        // Validación de campos
        $validatedData = $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'last_name_paternal' => 'sometimes|string|max:255',
            'last_name_maternal' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'email' => 'sometimes|email|unique:users,email,'.$id,
            'password' => 'sometimes|string|min:8|confirmed', 
        ]);
    
        // Buscar usuario
        $user = User::findOrFail($id);
    
        // Verificar permisos
        if (auth()->id() != $user->id && !auth()->user()->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para editar este perfil'
            ], 403);
        }
    
        // Encriptar contraseña si se proporciona
        if ($request->has('password')) {
            $validatedData['password'] = bcrypt($validatedData['password']);
        }
    
        // Actualizar usuario
        $user->update($validatedData);
    
        return response()->json([
            'success' => true,
            'message' => 'Perfil actualizado correctamente',
            'user' => $user
        ]);
    }

    // Eliminar un usuario (solo admin)
    public function destroy($id)
    {
        User::findOrFail($id)->delete();
        return response()->json(['message' => 'Usuario eliminado']);
    }
}