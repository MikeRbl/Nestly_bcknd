<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    //======================================================================
    // ACCIÓN DEL USUARIO AUTENTICADO
    //======================================================================

    /**
     * Devuelve los datos del usuario que realiza la petición.
     * Corresponde a la ruta: GET /api/user
     */
    public function me(Request $request)
    {
        // $request->user() devuelve la instancia del modelo del usuario autenticado.
        // Gracias al Accessor en el modelo User, la respuesta JSON ya incluirá 'avatar_url'.
        return response()->json($request->user());
    }


    //======================================================================
    // MÉTODOS CRUD ESTÁNDAR (Resource Controller)
    //======================================================================

    /**
     * Muestra una lista paginada de todos los usuarios.
     * Ruta: GET /api/users
     */
    public function index()
    {
        $this->authorize('viewAny', User::class);
        $users = User::paginate(15);
        return response()->json($users);
    }

    /**
     * Muestra los detalles de un usuario específico.
     * Ruta: GET /api/users/{user}
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);
        return response()->json($user);
    }

    public function store(Request $request)
    {
        // Primero, autorizamos si el usuario actual puede crear usuarios 
        $this->authorize('create', User::class);

        // Luego, validamos los datos de entrada
        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name_paternal' => 'required|string|max:255',
            'last_name_maternal' => 'sometimes|string|max:255',
            'email' => 'required|email|unique:users,email',
            // 'confirmed' asegura que el request incluya un campo 'password_confirmation' que coincida
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'sometimes|string|max:20',
        ]);
        
        // Hasheamos la contraseña antes de guardarla por seguridad
        $validatedData['password'] = Hash::make($validatedData['password']);

        // Creamos el usuario con los datos validados
        $user = User::create($validatedData);

        // Devolvemos el usuario recién creado con un código de estado 201 (Created)
        return response()->json($user, 201);
    }
    /**
     * Actualiza los datos de texto de un usuario específico.
     * La actualización del avatar se hace en un método separado.
     * Ruta: PUT /api/users/{user}
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $validatedData = $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'last_name_paternal' => 'sometimes|string|max:255',
            'last_name_maternal' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
        ]);

        $user->update($validatedData);

        return response()->json($user);
    }
    public function updateOwnProfile(Request $request)
        {
            $user = $request->user();

            $validatedData = $request->validate([
                'first_name' => 'sometimes|string|max:255',
                'last_name_paternal' => 'sometimes|string|max:255',
                'last_name_maternal' => 'sometimes|string|max:255',
                'phone' => 'sometimes|string|max:20',
                'email' => 'sometimes|email|unique:users,email,' . $user->id,
            ]);

            $user->update($validatedData);

            return response()->json($user);
        }

    /**
     * Elimina un usuario de la base de datos.
     * Ruta: DELETE /api/users/{user}
     */

public function destroy(User $user)
{
    Log::info('Método destroy llamado para el usuario: ' . $user->id);

    $user->delete();
    
    return response()->json(['message' => 'Usuario eliminado correctamente'], 200);
}


        public function deleteOwnAvatar(Request $request)
        {
            $user = $request->user();

            // Elimina el archivo físico si existe
            if ($user->avatar) {
                Storage::disk('public')->delete('avatars/' . $user->avatar);
            }

            // Limpia la referencia en BD
            $user->avatar = null;
            $user->save();

            return response()->json(['message' => 'Avatar eliminado correctamente', 'user' => $user]);
        }

    //======================================================================
    // GESTIÓN DEL AVATAR
    //======================================================================

    /**
     * Actualiza la foto de perfil (avatar) de un usuario.
     * Ruta: POST /api/users/{user}/avatar
     */
    public function updateAvatar(Request $request, User $user)
    {
        // Lógica de roles/permisos
        // if (Auth::id() !== $user->id && Auth::user()->role !== 'admin') { ... }

        $request->validate(['avatar' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048']);

        // Borramos el archivo físico anterior, si existe.
        if ($user->avatar) {
            Storage::disk('public')->delete('avatars/' . $user->avatar);
        }

        $file = $request->file('avatar');
        $filename = Str::uuid() . "." . $file->getClientOriginalExtension();
        $file->storeAs('public/avatars', $filename);

        $user->avatar = $filename;
        $user->save();
        
        return response()->json($user->fresh());
    }
        public function updateOwnAvatar(Request $request)
        {
            $user = $request->user();

            $request->validate([
                'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
            ]);

            if ($user->avatar) {
                Storage::disk('public')->delete('avatars/' . $user->avatar);
            }

            $file = $request->file('avatar');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/avatars', $filename);

            $user->avatar = $filename;
            $user->save();

            return response()->json($user->fresh());
        }

    /**
     * Elimina la foto de perfil (avatar) de un usuario.
     * Ruta: DELETE /api/users/{user}/avatar
     */
    public function deleteAvatar(User $user)
    {
        $user->deleteAvatar();

        return response()->json($user->fresh());
    }
    public function deleteOwnProfile(Request $request)
{
    $user = $request->user();
    $user->delete();
    return response()->json(['message' => 'Usuario eliminado correctamente'], 200);
}

}