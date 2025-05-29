<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;

class UserController extends Controller
{
    /**
     * Obtener todos los usuarios (solo admin)
     */
    public function index()
    {
        $this->authorize('viewAny', User::class);
        $users = User::all();
        return response()->json($users);
    }

    /**
     * Mostrar un usuario específico
     */
    public function show($id)
    {
        $user = User::findOrFail($id);
        $this->authorize('view', $user);
        return response()->json($user);
    }

    /**
     * Actualizar información de usuario
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $this->authorize('update', $user);

        $validatedData = $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'last_name_paternal' => 'sometimes|string|max:255',
            'last_name_maternal' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'email' => 'sometimes|email|unique:users,email,'.$id,
            'password' => 'sometimes|string|min:8|confirmed',
            'avatar' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            $this->updateUserAvatar($user, $request->file('avatar'));
        }

        if ($request->has('password')) {
            $validatedData['password'] = bcrypt($validatedData['password']);
        }

        $user->update($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Perfil actualizado correctamente',
            'user' => $user->fresh(),
            'avatar_url' => $user->avatar_url
        ]);
    }

    /**
     * Endpoint específico para actualizar foto de perfil
     */
    public function updateProfilePicture(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('profile_picture')) {
            $this->updateUserAvatar($user, $request->file('profile_picture'));
        }

        return response()->json([
            'success' => true,
            'message' => 'Foto de perfil actualizada correctamente',
            'profile_picture_url' => $user->fresh()->avatar_url
        ]);
    }

    /**
     * Eliminar un usuario (solo admin)
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $this->authorize('delete', $user);

        if ($user->avatar) {
            Storage::delete('public/avatars/' . $user->avatar);
        }

        $user->delete();
        return response()->json(['message' => 'Usuario eliminado']);
    }

    /**
     * Método privado para manejar la actualización de avatares
     */
    private function updateUserAvatar(User $user, $imageFile)
    {
        // Eliminar avatar anterior si existe
        if ($user->avatar) {
            Storage::delete('public/avatars/' . $user->avatar);
        }

        // Generar nombre único
        $filename = $user->id.'_'.time().'.'.$imageFile->extension();

        // Redimensionar y optimizar imagen
        $image = Image::make($imageFile)
            ->fit(200, 200, function ($constraint) {
                $constraint->upsize();
            })
            ->encode($imageFile->extension(), 85);

        // Guardar en storage
        Storage::put('public/avatars/'.$filename, $image);

        // Actualizar base de datos
        $user->avatar = $filename;
        $user->save();
    }
}