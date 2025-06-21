<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Esto detendrá la API y nos mostrará el rol del usuario y los roles permitidos.
        //dd(Auth::user()->role, $roles);

     
        $actualRole = strtolower(trim(Auth::user()->role));
        $allowedRoles = array_map('strtolower', array_map('trim', $roles));

        if (!Auth::check() || !in_array($actualRole, $allowedRoles)) {
            return response()->json(['message' => 'Acceso denegado...'], 403);
        }



        return $next($request);
    }
}
