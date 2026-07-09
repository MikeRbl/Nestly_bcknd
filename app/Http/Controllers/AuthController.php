<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Carbon\Carbon;

class AuthController extends Controller
{
    /**
     * Registra un nuevo usuario desde el formulario público.
     * Este es el método que soluciona el error.
     */
    public function register(Request $request)
    {
        // 1. Validar los datos que vienen del formulario de Angular
        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name_paternal' => 'required|string|max:255',
            'last_name_maternal' => 'required|string|max:255',
            'phone' => ['required', 'string', 'regex:/^[0-9]{10}$/'],
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
        ]);

        // 2. Crear el usuario en la base de datos
        $user = User::create([
            'first_name' => $validatedData['first_name'],
            'last_name_paternal' => $validatedData['last_name_paternal'],
            'last_name_maternal' => $validatedData['last_name_maternal'],
            'phone' => $validatedData['phone'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'role' => 'inquilino', // Asignar rol por defecto
        ]);

        // 3. Devolver una respuesta exitosa
        return response()->json([
            'message' => 'Usuario registrado exitosamente',
            'user' => $user
        ], 201);
    }

    /**
     * Inicia sesión para un usuario.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 1. Busca al usuario por su email
        $user = User::where('email', $credentials['email'])->first();

        // 2. Verifica si el usuario no existe o la contraseña es incorrecta
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Las credenciales proporcionadas son incorrectas.'], 401);
        }

        // 3. Verifica el estado del usuario
        if ($user->status === 'suspendido') {
            // Si la suspensión ya terminó, lo reactiva
            if ($user->suspension_ends_at && Carbon::now()->isAfter($user->suspension_ends_at)) {
                $user->status = 'activo';
                $user->suspension_ends_at = null;
                $user->save();
            } else {
                // Si sigue suspendido, devuelve el error 403 con los detalles que el frontend espera
                return response()->json([
                    'message' => 'Tu cuenta está suspendida.',
                    'suspension_ends_at' => $user->suspension_ends_at
                ], 403);
            }
        }

        if ($user->status === 'baneado') {
            return response()->json(['message' => 'Tu cuenta ha sido baneada permanentemente.'], 403);
        }

        // 4. Si todo está bien, crea el token y devuelve la respuesta de éxito
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'user' => $user,
        ]);
    }

    /**
     * Cierra la sesión del usuario.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Sesión cerrada exitosamente']);
    }

    /**
     * Devuelve los datos del usuario autenticado.
     */
    public function user(Request $request)
    {
        return response()->json($request->user());
    }
    public function logData(Request $request)
    {
        $user = $request->user();
    
        return response()->json(['user' => $user] );
    }
    
}
