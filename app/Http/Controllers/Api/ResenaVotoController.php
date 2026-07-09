<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Resena;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResenaVotoController extends Controller
{
    public function toggle(Resena $resena)
    {
        $user = Auth::user();

        // Si el voto no existe, lo crea.
        // Si ya existe, lo borra.
        $user->resenasVotadas()->toggle($resena->id);

        // Devolvemos el nuevo número de votos.
        return response()->json([
            'message' => 'Operación exitosa.',
            'votos_count' => $resena->fresh()->votos_count
        ]);
    }
}