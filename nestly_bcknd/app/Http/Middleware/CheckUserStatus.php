<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckUserStatus
{
    public function handle(Request $request, Closure $next)
{
    $user = $request->user();

    if ($user && $user->status === 'baneado') {
        \Log::warning("Usuario baneado intentó acceder: {$user->email}");

        // Eliminar token actual para "logout"
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Tu cuenta ha sido baneada. Contacta al administrador.'
        ], 403);
    }

    return $next($request);
}


}
