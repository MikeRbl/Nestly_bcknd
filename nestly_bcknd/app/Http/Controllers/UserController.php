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
    // MÉTODOS CRUD ESTÁNDAR (Resource Controller)
    //======================================================================

    /**
     * Muestra una lista paginada y filtrada de todos los usuarios.
     * Ruta: GET /api/users
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        // Inicia la consulta base
        $query = User::query();

        // Filtro de búsqueda (por nombre, apellido o email)
        $query->when($request->query('search'), function ($q, $search) {
            return $q->where(function($subQuery) use ($search) {
                $subQuery->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name_paternal', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
            });
        });

        // Filtro por rol
        $query->when($request->query('role'), function ($q, $role) {
            return $q->where('role', $role);
        });

        // Ordena por los más recientes y pagina los resultados
        $users = $query->latest()->paginate($request->query('per_page', 10));

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
        $this->authorize('create', User::class);

        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name_paternal' => 'required|string|max:255',
            'last_name_maternal' => 'sometimes|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'sometimes|string|max:20',
        ]);
        
        $validatedData['password'] = Hash::make($validatedData['password']);
        $user = User::create($validatedData);

        return response()->json($user, 201);
    }
    
    /**
     * Actualiza los datos de texto de un usuario específico.
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

    /**
     * Elimina un usuario de la base de datos.
     * Ruta: DELETE /api/users/{user}
     */
     public function destroy(User $user)
    {
        // 1. Autorización: Verifica si el usuario actual tiene permiso para eliminar.
        $this->authorize('delete', $user);

        try {
            Log::info('Solicitud para eliminar al usuario: ' . $user->id);

            // 2. Elimina el usuario.
            $user->delete();
            
            // 3. Devuelve una respuesta de éxito.
            return response()->json(['message' => 'Usuario eliminado correctamente'], 200);

        } catch (\Exception $e) {
            Log::error("Error al eliminar el usuario {$user->id}: " . $e->getMessage());
            return response()->json(['message' => 'Ocurrió un error en el servidor.'], 500);
        }
    }


    //======================================================================
    // ACCIONES DEL USUARIO AUTENTICADO
    //======================================================================

    public function me(Request $request)
    {
        return response()->json($request->user());
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

    public function deleteOwnProfile(Request $request)
    {
        $user = $request->user();
        $user->delete();
        return response()->json(['message' => 'Usuario eliminado correctamente'], 200);
    }

    //======================================================================
    // GESTIÓN DEL AVATAR
    //======================================================================

    public function updateOwnAvatar(Request $request)
    {
        $user = $request->user();
        $request->validate(['avatar' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048']);
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

    public function deleteOwnAvatar(Request $request)
    {
        $user = $request->user();
        if ($user->avatar) {
            Storage::disk('public')->delete('avatars/' . $user->avatar);
        }
        $user->avatar = null;
        $user->save();
        return response()->json(['message' => 'Avatar eliminado correctamente', 'user' => $user]);
    }
    public function cambiarEstadoUsuario(Request $request, User $user)
{
    // 1. Autorizar que solo admin puede hacer esto
    $this->authorize('updateStatus', $user);

    // 2. Validar el input (solo 'activo' o 'baneado')
    $validated = $request->validate([
        'status' => 'required|in:activo,baneado',
    ]);

    // 3. Cambiar el estado y guardar
    $user->status = $validated['status'];
    $user->save();

    return response()->json(['message' => 'Estado actualizado correctamente', 'user' => $user]);
}

}
