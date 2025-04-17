<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Registro de usuario
    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name_paternal' => 'required|string|max:255',
            'last_name_maternal' => 'required|string|max:255',
            'phone' => 'required|string|max:15',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'sometimes|in:inquilino,propietario,admin',
        ]);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name_paternal' => $request->last_name_paternal,
            'last_name_maternal' => $request->last_name_maternal,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role ?? 'inquilino',
        ]);

        return response()->json([
            'message' => 'Usuario registrado exitosamente',
            'user' => $user
        ], 201);
    }

    // Login de usuario
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
    
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('authToken')->plainTextToken;
            
            return response()->json([
                'estatus' => true,
                'access_token' => $token,
                'user' => [
                    'email' => $user->email,
                    'name' => $user->name
                ]
            ]);
        }
    
        return response()->json([
            'estatus' => false,
            'message' => 'Credenciales inválidas'
        ], 401);
    }

    // Logout de usuario
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Sesión cerrada']);
    }

    public function logData(Request $request)
    {
        $user = $request->user();
    
        return response()->json(['user' => $user] );
    }



    public function update(Request $request, $id)
    {
        // 1. Validar los datos de entrada
        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|string|max:255',
            'last_name_paternal' => 'sometimes|string|max:255',
            'last_name_maternal' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,'.$id,
            'phone' => 'sometimes|string|max:20',
            'password' => 'sometimes|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // 2. Buscar el usuario
        $user = User::find($id);
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        // 3. Preparar los datos para actualizar
        $updateData = $request->only([
            'first_name',
            'last_name_paternal',
            'last_name_maternal',
            'email',
            'phone'
        ]);

        // 4. Manejar la contraseña si viene en la solicitud
        if ($request->has('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        // 5. Actualizar el usuario
        $user->update($updateData);

        // 6. Retornar respuesta exitosa
        return response()->json([
            'success' => true,
            'message' => 'Usuario actualizado correctamente',
            'user' => $user->fresh() // Retorna el usuario con los datos actualizados
        ]);
    }
}