<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str; // Para generar nombres de archivo únicos si lo prefieres

class UserController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', User::class);
        $users = User::all();
        return response()->json($users);
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        $this->authorize('view', $user);
        return response()->json($user);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $this->authorize('update', $user);

        $validatedDataRules = [
            'first_name' => 'sometimes|string|max:255',
            'last_name_paternal' => 'sometimes|string|max:255',
            'last_name_maternal' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'email' => 'sometimes|email|unique:users,email,'.$id,
            'password' => 'sometimes|string|min:8|confirmed',
            'avatar' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];
        $validatedData = $request->validate($validatedDataRules);

        if ($request->hasFile('avatar')) {
            $this->updateUserAvatar($user, $request->file('avatar'));
            unset($validatedData['avatar']); 
        }

        if (!empty($validatedData['password'])) {
            $validatedData['password'] = bcrypt($validatedData['password']);
        } else {
            unset($validatedData['password']);
        }

        if (count($validatedData) > 0) {
            $user->update($validatedData);
        }

        return response()->json([
            'success' => true,
            'message' => 'Perfil actualizado correctamente',
            'user' => $user->fresh(),
        ]);
    }

    public function updateProfilePicture(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
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

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $this->authorize('delete', $user);
        
        // El evento 'deleting' en tu modelo User debería encargarse de llamar a $user->deleteAvatar()
        $user->delete(); 
        
        return response()->json(['message' => 'Usuario eliminado']);
    }

    
    // En tu controlador, modifica el método updateUserAvatar:
        private function updateUserAvatar(User $user, $imageFile)
        {
            // Eliminar avatar anterior si existe
            if ($user->avatar) {
                Storage::disk('public')->delete('avatars/' . $user->avatar);
            }

            // Generar nombre único
            $filename = Str::uuid() . '.' . $imageFile->getClientOriginalExtension();

            // Guardar en storage (asegúrate que la carpeta avatars existe)
            $path = $imageFile->storeAs('public/avatars', $filename);

            // Actualizar base de datos
            $user->avatar = $filename;
            $user->save();

            return $path; // Para debugging
        }
    // Elimina específicamente la foto de perfil
        public function deleteProfilePicture($id)
{
    $user = User::findOrFail($id);
    
    // Verificación manual (sin políticas)
    if (auth()->id() !== $user->id) {
        abort(403, 'No tienes permiso para esta acción');
    }

    if ($user->avatar) {
        Storage::disk('public')->delete('avatars/'.$user->avatar);
        $user->avatar = null;
        $user->save();
    }

    return response()->json([
        'success' => true,
        'message' => 'Foto eliminada',
        'avatar_url' => $user->avatar_url // URL de imagen por defecto
    ]);
}
}

