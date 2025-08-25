<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CheckUserStatus
{
     public function handle(Request $request, Closure $next)
    {
        // Usamos auth()->user() para obtener el usuario autenticado
        $user = auth()->user();

        if ($user) {
            // Caso 1: El usuario está suspendido
            if ($user->status === 'suspendido') {
                // Si la fecha de suspensión ya pasó, lo reactivamos
                if ($user->suspension_ends_at && Carbon::now()->isAfter($user->suspension_ends_at)) {
                    $user->status = 'activo';
                    $user->suspension_ends_at = null;
                    $user->save();
                } else {
                    // Si la suspensión sigue activa, devolvemos un error 403 con los detalles
                    return response()->json([
                        'message' => 'Tu cuenta está suspendida.',
                        'suspension_ends_at' => $user->suspension_ends_at
                    ], 403);
                }
            }

            // Caso 2: El usuario está baneado permanentemente
            if ($user->status === 'baneado') {
                return response()->json(['message' => 'Tu cuenta ha sido baneada permanentemente.'], 403);
            }
        }

        return $next($request);
    }
}
