<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Propiedad;
use Illuminate\Support\Facades\Storage;

class PropiedadController extends Controller
{
    public function store(Request $request)
{
    $validated = $request->validate([
        'titulo' => 'required|string|max:255',
        'descripcion' => 'required|string',
        'direccion' => 'required|string|max:255',
        'pais' => 'required|string|max:100',
        'estado' => 'required|string|max:100',
        'ciudad' => 'required|string|max:100',
        'colonia' => 'nullable|string|max:100',
        'precio' => 'required|numeric',
        'habitaciones' => 'required|integer|min:0',
        'banos' => 'required|integer|min:0',
        'metros_cuadrados' => 'required|integer|min:0',
        'amueblado' => 'required|boolean',
        'disponibilidad' => 'required|boolean',
        'email' => 'required|email|max:255',
        'telefono' => 'required|string|max:15',
        'apartamento' => 'nullable|boolean',
        'casaPlaya' => 'nullable|boolean',
        'industrial' => 'nullable|boolean',
        'anualizado' => 'nullable|boolean',
        'deposito' => 'nullable|numeric',
        'mascotas' => 'required|in:si,no',
        'tamano' => 'nullable|integer',
        'fotos' => 'nullable|array',
        'fotos.*' => 'image|mimes:jpg,jpeg,png,gif,svg|max:2048',
    ]);

    try {
        $uploadedPhotos = [];

        if ($request->hasFile('fotos')) {
            foreach ($request->file('fotos') as $photo) {
                $path = $photo->store('propiedades', 'public');
                $uploadedPhotos[] = Storage::url($path);
            }
        }

        $validated['fotos'] = !empty($uploadedPhotos) ? json_encode($uploadedPhotos) : null;
        $validated['id_propietario'] = auth()->id();

        $propiedad = Propiedad::create($validated);

        return response()->json([
            'success' => true,
            'data' => $propiedad,
            'message' => 'Propiedad creada exitosamente'
        ], 201);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al crear la propiedad',
            'error' => $e->getMessage()
        ], 500);
    }
}
public function show($id)
{
    try {
        // Buscar la propiedad por su ID
        $propiedad = Propiedad::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $propiedad,
            'message' => 'Propiedad encontrada'
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Propiedad no encontrada',
            'error' => $e->getMessage()
        ], 404);
    }
}
public function update(Request $request, $id)
{
    // Validación de los datos
    $validated = $request->validate([
        'titulo' => 'required|string|max:255',
        'descripcion' => 'required|string',
        'direccion' => 'required|string|max:255',
        'pais' => 'required|string|max:100',
        'estado' => 'required|string|max:100',
        'ciudad' => 'required|string|max:100',
        'colonia' => 'nullable|string|max:100',
        'precio' => 'required|numeric',
        'habitaciones' => 'required|integer|min:0',
        'banos' => 'required|integer|min:0',
        'metros_cuadrados' => 'required|integer|min:0',
        'amueblado' => 'required|boolean',
        'disponibilidad' => 'required|boolean',
        'email' => 'required|email|max:255',
        'telefono' => 'required|string|max:15',
        'apartamento' => 'nullable|boolean',
        'casaPlaya' => 'nullable|boolean',
        'industrial' => 'nullable|boolean',
        'anualizado' => 'nullable|boolean',
        'deposito' => 'nullable|numeric',
        'mascotas' => 'required|in:si,no',
        'tamano' => 'nullable|integer',
        'fotos' => 'nullable|array',
        'fotos.*' => 'image|mimes:jpg,jpeg,png,gif,svg|max:2048',
    ]);

    try {
        // Buscar la propiedad que quiere actualizar
        $propiedad = Propiedad::findOrFail($id);

        // Verificar si el usuario es el dueño de la propiedad
        if ($propiedad->id_propietario !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para actualizar esta propiedad'
            ], 403);
        }

        // Manejo de fotos (si se suben nuevas)
        $uploadedPhotos = [];
        if ($request->hasFile('fotos')) {
            foreach ($request->file('fotos') as $photo) {
                $path = $photo->store('propiedades', 'public');
                $uploadedPhotos[] = Storage::url($path);
            }
        }

        // Actualizar los datos
        $propiedad->update(array_merge($validated, [
            'fotos' => !empty($uploadedPhotos) ? json_encode($uploadedPhotos) : $propiedad->fotos,
        ]));

        return response()->json([
            'success' => true,
            'data' => $propiedad,
            'message' => 'Propiedad actualizada exitosamente'
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al actualizar la propiedad',
            'error' => $e->getMessage()
        ], 500);
    }
}

}