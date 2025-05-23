<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image; // Para redimensionar imágenes

class UserController extends Controller
{
    // Obtener todos los usuarios (solo admin)
    public function index()
    {
        $this->authorize('viewAny', User::class); // Política de acceso
        $users = User::all();
        return response()->json($users);
    }

    // Mostrar un usuario específico
    public function show($id)
    {
        $user = User::findOrFail($id);
        $this->authorize('view', $user); // Política de acceso
        return response()->json($user);
    }

    // Actualizar un usuario (incluyendo avatar)
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        // Verificar permisos (solo el propio usuario o un admin puede editar)
        $this->authorize('update', $user);

        // Validación de campos
        $validatedData = $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'last_name_paternal' => 'sometimes|string|max:255',
            'last_name_maternal' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'email' => 'sometimes|email|unique:users,email,'.$id,
            'password' => 'sometimes|string|min:8|confirmed',
            'avatar' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048', // 2MB máximo
        ]);

        // Procesar avatar si se envió
        if ($request->hasFile('avatar')) {
            $this->updateAvatar($user, $request->file('avatar'));
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
            'user' => $user->fresh(), // Devuelve el usuario con los cambios actualizados
            'avatar_url' => $user->avatar_url // Incluye la URL del avatar
        ]);
    }

    // Eliminar un usuario (solo admin)
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $this->authorize('delete', $user); // Política de acceso

        // Eliminar avatar si existe
        if ($user->avatar) {
            Storage::delete('public/avatars/' . $user->avatar);
        }

        $user->delete();
        return response()->json(['message' => 'Usuario eliminado']);
    }

    // Método privado para actualizar avatar (reutilizable)
    private function updateAvatar(User $user, $avatarFile)
    {
        // Eliminar avatar anterior si existe
        if ($user->avatar) {
            Storage::delete('public/avatars/' . $user->avatar);
        }

        // Generar nombre único y guardar
        $filename = $user->id . '_' . time() . '.' . $avatarFile->extension();
        
        // Redimensionar y guardar (opcional)
        $image = Image::make($avatarFile)->fit(200, 200);
        $image->save(storage_path('app/public/avatars/' . $filename));

        $user->avatar = $filename;
        $user->save();
    }
}