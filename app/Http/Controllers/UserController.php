<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * (Admin) Muestra una lista de todos los usuarios.
     */
public function index(Request $request)
    {
        
        $this->authorize('viewAny', User::class);

        $query = User::query();

        // Filtro de búsqueda (sin cambios)
        $query->when($request->search, function ($q, $search) {
            $q->where(function ($subQuery) use ($search) {
                $subQuery->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name_paternal', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
            });
        });

        // Filtro por rol 
        $query->when($request->role, function ($q, $role) {
            $q->where('role', $role);
        });
        
        // Filtro por estado 
        $query->when($request->status, function ($q, $status) {
            $q->where('status', $status);
        });

        $users = $query->latest()->paginate($request->get('per_page', 10));

        return response()->json($users);
    }

    /**
     * (Admin) Crea un nuevo usuario.
     */
    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name_paternal' => 'required|string|max:255',
            'last_name_maternal' => 'required|string|max:255',
            'phone' => ['required', 'string', 'regex:/^[0-9]{10}$/'],
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
            'role' => 'sometimes|string|in:admin,propietario,inquilino',
        ]);

        $user = User::create([
            'first_name' => $validatedData['first_name'],
            'last_name_paternal' => $validatedData['last_name_paternal'],
            'last_name_maternal' => $validatedData['last_name_maternal'],
            'phone' => $validatedData['phone'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'role' => $validatedData['role'] ?? 'inquilino',
        ]);

        return response()->json($user, 201);
    }

    /**
     * (Admin) Muestra un usuario específico.
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);
        return response()->json($user);
    }

    /**
     * (Admin) Actualiza un usuario.
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $validatedData = $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'last_name_paternal' => 'sometimes|string|max:255',
            'last_name_maternal' => 'sometimes|string|max:255',
            'phone' => ['sometimes', 'string', 'regex:/^[0-9]{10}$/'],
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'sometimes|string|in:admin,propietario,inquilino',
        ]);

        $user->update($validatedData);

        return response()->json($user);
    }
    public function suspenderUsuarioReportado(Request $request, Reporte $reporte)
    {
        $this->authorize('update', $reporte);

        // Validar que se envíe la duración
        $validated = $request->validate([
            'suspension_duration_days' => 'required|integer|min:1',
        ]);

        if (! $reporte->reportable instanceof User) {
            return response()->json(['message' => 'El reporte no es sobre un usuario'], 400);
        }

        $usuario = $reporte->reportable;

        // Actualizar el estado y la fecha de fin de suspensión
        $usuario->status = 'suspendido';
        $usuario->suspension_ends_at = now()->addDays($validated['suspension_duration_days']);
        $usuario->save();

        // Marcar el reporte como resuelto
        $reporte->estado = 'resuelto';
        $reporte->save();

        return response()->json([
            'message' => 'Usuario suspendido y reporte resuelto',
            'usuario' => $usuario,
            'reporte' => $reporte
        ]);
    }

    /**
     * (Admin) Elimina un usuario.
     */
    public function destroy(User $user)
{
    $this->authorize('delete', $user);

    // 1. Llama a tu método para borrar el avatar primero
    // (Este método se encargará de guardar el cambio del avatar)
    $user->deleteAvatar();

    // 2. Luego, realiza el borrado lógico del usuario
    $user->delete();

    return response()->json(['message' => 'Usuario eliminado correctamente']);
}

    // ===================================================================
    // MÉTODOS RESTAURADOS PARA LA GESTIÓN DEL PERFIL PROPIO
    // ===================================================================

    /**
     * El usuario autenticado actualiza su propio avatar.
     */
    public function updateOwnProfile(Request $request)
{
    $user = auth()->user();

    $validatedData = $request->validate([
        'first_name' => 'required|string|max:255',
        'last_name_paternal' => 'required|string|max:255',
        'last_name_maternal' => 'required|string|max:255',
        'phone' => ['required', 'string', 'regex:/^[0-9]{10}$/'],
        'email' => 'required|email|max:255|unique:users,email,' . $user->id,
        'password' => ['nullable', 'string', Password::min(6)],
        'password_confirmation' => ['nullable', 'same:password'],
    ]);

    // Actualiza los campos básicos
    $user->first_name = $validatedData['first_name'];
    $user->last_name_paternal = $validatedData['last_name_paternal'];
    $user->last_name_maternal = $validatedData['last_name_maternal'];
    $user->phone = $validatedData['phone'];
    $user->email = $validatedData['email'];

    // Actualiza la contraseña si se proporciona
    if (!empty($validatedData['password'])) {
        $user->password = Hash::make($validatedData['password']);
    }

    $user->save();

    return response()->json([
        'message' => 'Perfil actualizado correctamente.',
        'user' => $user->fresh()
    ]);
}
    public function updateOwnAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $user = $request->user();

        // Elimina el avatar anterior si existe
        if ($user->avatar) {
            Storage::disk('public')->delete('avatars/' . $user->avatar);
        }

        // Guarda el nuevo avatar
        $file = $request->file('avatar');
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $file->storeAs('public/avatars', $filename);

        $user->avatar = $filename;
        $user->save();

        return response()->json($user->fresh());
    }

    /**
     * El usuario autenticado elimina su propio avatar.
     */
    public function deleteOwnAvatar(Request $request)
    {
        $user = $request->user();

        if ($user->avatar) {
            Storage::disk('public')->delete('avatars/' . $user->avatar);
            $user->avatar = null;
            $user->save();
        }

        return response()->json(['message' => 'Avatar eliminado correctamente', 'user' => $user]);
    }
    public function updateRole(Request $request, User $user)
    {
        $this->authorize('update', $user);
        $validated = $request->validate(['role' => 'required|string|in:admin,propietario,inquilino']);
        $user->update($validated);
        return response()->json(['message' => 'Rol actualizado.', 'user' => $user]);
    }

/**
 * (Admin) Actualiza el estado de un usuario específico (activo/baneado).
 */
public function updateStatus(Request $request, User $user)
{
    $this->authorize('update', $user);

    $validated = $request->validate([
        'status' => ['required', 'string', Rule::in(['activo', 'suspendido', 'baneado'])],
        'suspension_duration_days' => ['nullable', 'integer', 'min:1'],
    ]);

    $user->status = $validated['status'];
    
    if ($user->status === 'suspendido' && isset($validated['suspension_duration_days'])) {
        $user->suspension_ends_at = now()->addDays($validated['suspension_duration_days']);
    } else {
        // Si el estado es 'activo' o 'baneado', la suspensión se anula.
        $user->suspension_ends_at = null;
    }

    $user->save();

    return response()->json(['message' => 'Estado actualizado.', 'user' => $user]);
}

public function destroyOwnAccount(Request $request)
    {
        $request->validate(['password' => 'required|string']);
        $user = $request->user();

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'La contraseña es incorrecta.'], 422);
        }

        $user->tokens()->delete(); // Invalida tokens de sesión
        $user->delete(); // Soft delete

        return response()->json(['message' => 'Tu cuenta ha sido eliminada.']);
    }
}
