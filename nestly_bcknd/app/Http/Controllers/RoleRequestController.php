<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RoleRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RoleRequestController extends Controller
{
    // Usuario crea una solicitud
    public function store(Request $request)
    {
        $user = $request->user();

        // Evitar duplicados pendientes
        $exists = RoleRequest::where('user_id', $user->id)
            ->where('status', 'pendiente')
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Ya tienes una solicitud pendiente'], 400);
        }

        $roleRequest = RoleRequest::create([
            'user_id' => $user->id,
            'requested_role' => 'propietario',
            'status' => 'pendiente',
        ]);

        return response()->json($roleRequest, 201);
    }

    // Admin aprueba o rechaza
    public function update(Request $request, RoleRequest $roleRequest)
{
    $request->validate([
        'status' => 'required|in:aprobado,rechazado',
    ]);

    // Añade esta validación para evitar procesar una solicitud ya finalizada
    if ($roleRequest->status !== 'pendiente') {
        return response()->json(['message' => 'Esta solicitud ya ha sido procesada.'], 409); // 409 Conflict
    }

    try {
        DB::transaction(function () use ($request, $roleRequest) {
            $roleRequest->status = $request->status;
            $roleRequest->save();

            // Si se aprueba, actualiza el rol del usuario DENTRO de la transacción
            if ($request->status === 'aprobado') {
                // No es necesario volver a buscar el usuario, ya está cargado
                $roleRequest->user->role = $roleRequest->requested_role;
                $roleRequest->user->save();
            }
        });
    } catch (\Exception $e) {
        // Si algo falla, retorna un error de servidor
        return response()->json(['message' => 'Ocurrió un error al procesar la solicitud.'], 500);
    }

    // Carga de nuevo la relación para que la respuesta JSON incluya los datos del usuario actualizados
    $roleRequest->load('user');

    return response()->json($roleRequest);
}

    // Admin lista todas las solicitudes
    public function index()
    {
        $requests = RoleRequest::with('user')->orderByDesc('created_at')->get();
        return response()->json($requests);
    }
}
