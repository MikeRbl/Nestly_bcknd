<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Testimonio;
use Illuminate\Support\Facades\Auth;

class TestimonioController extends Controller
{
    /**
     * Obtener todos los testimonios.
     * Usamos with('usuario') para cargar la relación y evitar problemas de N+1.
     */
    public function index()
    {
        $testimonios = Testimonio::with('usuario')->latest()->get();
        return response()->json(['data' => $testimonios]);
    }

    /**
     * Crear un nuevo testimonio.
     */
    public function store(Request $request)
    {
        $request->validate([
            'comentario' => 'required|string|max:1000',
            'puntuacion' => 'required|integer|min:1|max:5',
        ]);

        $user = Auth::user();
        $testimonio = Testimonio::create([
            'id_usuario' => $user->id,
            'comentario' => $request->comentario,
            'puntuacion' => $request->puntuacion,
        ]);

        return response()->json($testimonio->load('usuario'), 201);
    }

    /**
     * Actualizar un testimonio existente.
     */
    public function update(Request $request, Testimonio $testimonio)
    {
        $user = Auth::user();
        if (!$user || $testimonio->id_usuario !== $user->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $validatedData = $request->validate([
            'comentario' => 'sometimes|required|string|max:1000',
            'puntuacion' => 'sometimes|required|integer|min:1|max:5',
        ]);

        $testimonio->update($validatedData);

        return response()->json($testimonio->load('usuario'));
    }

    /**
     * Eliminar un testimonio.
     */
    public function destroy(Testimonio $testimonio)
    {
        $user = Auth::user();
        if (!$user || $testimonio->id_usuario !== $user->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $testimonio->delete();
        return response()->json(['message' => 'Testimonio eliminado']);
    }
}
