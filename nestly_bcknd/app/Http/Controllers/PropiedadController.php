<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PropiedadController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'pais' => 'required|string|max:100',
            'estado' => 'required|string|max:100',
            'ciudad' => 'required|string|max:100',
            'colonia' => 'required|string|max:100',
            'precio' => 'required|numeric',
            'habitaciones' => 'required|integer',
            'banos' => 'required|integer',
            'metros_cuadrados' => 'required|integer',
            'amueblado' => 'required|boolean',
            'disponibilidad' => 'required|string|max:20',
            'fotos' => 'nullable|string' 
        ]);

        $propiedad = Propiedad::create([
            ...$validated,
            'id_propietario' => auth()->id() 
        ]);

        return response()->json($propiedad, 201);
    }
    public function index()
    {
        $propiedades = Propiedad::all();
        return response()->json($propiedades);
    }
    public function show($id)
    {
        $propiedad = Propiedad::findOrFail($id);
        return response()->json($propiedad);
    } 


    public function update(Request $request, $id)
    {
        $propiedad = Propiedad::findOrFail($id);
        $validated = $request->validate([
            'pais' => 'sometimes|required|string|max:100',
            'estado' => 'sometimes|required|string|max:100',
            'ciudad' => 'sometimes|required|string|max:100',
            'colonia' => 'sometimes|required|string|max:100',
            'precio' => 'sometimes|required|numeric',
            'habitaciones' => 'sometimes|required|integer',
            'banos' => 'sometimes|required|integer',
            'metros_cuadrados' => 'sometimes|required|integer',
            'amueblado' => 'sometimes|required|boolean',
            'disponibilidad' => 'sometimes|required|string|max:20',
            'fotos' => 'nullable|string'
        ]);

        $propiedad->update($validated);

        return response()->json($propiedad);
    }
    public function destroy($id)
    {
        $propiedad = Propiedad::findOrFail($id);
        $propiedad->delete();

        return response()->json(['message' => 'Propiedad eliminada']);
    }
}