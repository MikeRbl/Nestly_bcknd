<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Propiedad;
use App\Models\Resena;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ResenaController extends Controller
{
    //* ================================ INDEX ======================================= */
    public function index(Propiedad $propiedad)
    {
        $resenas = $propiedad->resenas()
            ->with('user:id,first_name,last_name_paternal,avatar') // Carga los datos del usuario que hizo la reseña, incluyendo nombre y avatar
            ->latest() // Ordena las reseñas de la más nueva a la más vieja
            ->paginate(10); // Pagina los resultados para no sobrecargar la respuesta

        return response()->json($resenas);
    }
    //* ================================ INDEX ======================================= */

    //* ================================ STORE ======================================= */
    public function store(Request $request, Propiedad $propiedad)
    {
        $request->validate([
            'puntuacion' => 'required|integer|between:1,5',
            'comentario' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();

       
        // a) El propietario no puede reseñar su propia propiedad
        if ($propiedad->id_propietario === $user->id) {
            return response()->json([
                'message' => 'No puedes crear una reseña para tu propia propiedad.'
            ], 403);
        }

        // b) El usuario no puede crear más de una reseña para la misma propiedad
        $reseñaExistente = $propiedad->resenas()->where('user_id', $user->id)->exists();
        if ($reseñaExistente) {
            return response()->json([
                'message' => 'Ya has creado una reseña para esta propiedad.'
            ], 409);
        }

        // Crear la reseña
        $resena = $propiedad->resenas()->create([
            'user_id' => $user->id,
            'puntuacion' => $request->puntuacion,
            'comentario' => $request->comentario,
        ]);

        return response()->json($resena->load('user'), 201);
    }

    //* ================================ STORE ======================================= */


    //* ================================ SHOW ======================================= */
    public function show(Resena $resena)
    {
        // Cargamos las relaciones con el usuario y la propiedad para dar una respuesta más completa.
        $resena->load([
            'user:id,first_name,last_name_paternal,avatar', 
            'propiedad:id_propiedad,titulo,direccion'
        ]);

        return response()->json($resena);
    }
    //* ================================ SHOW ======================================= */
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Resena  $resena
     * @return \Illuminate\Http\Response
     */
    //* ================================ UPDATE ======================================= */

    public function update(Request $request, Resena $resena)
    {
        // 1. Autorización: Solo el autor original puede editar su reseña.
        if (Auth::id() !== $resena->user_id) {
            return response()->json(['message' => 'No autorizado'], 403); // 403 Forbidden
        }

        // 2. Validar los datos de entrada
        $validatedData = $request->validate([
            'puntuacion' => 'sometimes|required|integer|between:1,5',
            'comentario' => 'sometimes|nullable|string|max:1000',
        ]);

        // 3. Actualizar la reseña
        $resena->update($validatedData);

        // 4. Devolver la respuesta con la reseña actualizada
        // Cargamos la relación para devolver el objeto completo y actualizado.
        return response()->json($resena->load('user'), 200); // 200 OK
    }
    //* ================================ UPDATE ======================================= */
    
    //* ================================ DESTROY ======================================= */

    public function destroy(Resena $resena)
    {
        // 1. Autorización: Verificar si el usuario actual es el autor de la reseña O si es un administrador.
        $user = Auth::user();

        if ($user->id !== $resena->user_id && $user->role !== 'admin') {
            return response()->json(['message' => 'No autorizado'], 403); // 403 Forbidden
        }

        // 2. Eliminar la reseña
        $resena->delete();

        // 3. Devolver una respuesta vacía con éxito
        return response()->noContent(); // 204 No Content
    }
    //* ================================ DESTROY ======================================= */
}
