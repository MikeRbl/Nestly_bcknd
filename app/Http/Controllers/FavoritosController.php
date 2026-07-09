<?php

namespace App\Http\Controllers;

use App\Models\Propiedad;
use App\Models\Favorito;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoritosController extends Controller
{
    /**
     * Añade una propiedad a la lista de favoritos del usuario.
     */
    public function store($propiedadId)
    {
        $user = Auth::user();
        
        $favorito = $user->favoritos()->firstOrCreate(['propiedad_id' => $propiedadId]);

        if (!$favorito->wasRecentlyCreated) {
            return response()->json(['message' => 'Esta propiedad ya está en tus favoritos.'], 409);
        }

        return response()->json([
            'message' => 'Propiedad añadida a favoritos exitosamente!',
            'favorito' => $favorito
        ], 201);
    }

    /**
     * Elimina una propiedad de la lista de favoritos del usuario.
     */
    public function destroy($propiedadId)
    {
        $user = Auth::user();

        $deletedCount = $user->favoritos()->where('propiedad_id', $propiedadId)->delete();

        if ($deletedCount === 0) {
            return response()->json(['message' => 'Esta propiedad no se encuentra en tus favoritos.'], 404);
        }

        return response()->json(['message' => 'Propiedad eliminada de favoritos exitosamente!'], 200);
    }

    /**
     * Muestra todas las propiedades favoritas del usuario.
     */
    public function index()
    {
        $user = Auth::user();
        
        $propiedadesFavoritas = $user->favoritos()
                                     ->with(['propiedad.tipoPropiedad', 'propiedad.propietario'])
                                     ->get()
                                     ->pluck('propiedad')
                                     ->filter();

        return response()->json(['data' => $propiedadesFavoritas]);
    }
    
    /**
     * Muestra solo los IDs de las propiedades favoritas del usuario.
     */
    public function indexIds()
    {
        $user = Auth::user();
        
        $favoritoIds = $user->favoritos()->pluck('propiedad_id');

        return response()->json(['data' => $favoritoIds]);
    }
}
