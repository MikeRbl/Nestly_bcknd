<?php

namespace App\Http\Controllers;

use App\Models\Propiedad;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PropiedadController extends Controller
{
    /**
     * Lista todas las propiedades o las filtra por el ID del propietario.
     */
    public function index(Request $request)
    {
        try {
            $query = Propiedad::with('tipoPropiedad')->orderBy('created_at', 'desc');

            if ($request->has('id_propietario')) {
                $query->where('id_propietario', $request->query('id_propietario'));
            }

            $propiedades = $query->paginate($request->input('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $propiedades,
                'message' => 'Propiedades recuperadas exitosamente.'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error en PropiedadController@index: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error del servidor al recuperar las propiedades.'], 500);
        }
    }

    /**
     * Lista todas las propiedades que pertenecen a un usuario específico.
     */
    public function indexByUser($userId, Request $request)
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Usuario no encontrado.'], 404);
            }
            
            $propiedades = Propiedad::with('tipoPropiedad')
                                      ->where('id_propietario', $userId)
                                      ->orderBy('created_at', 'desc')
                                      ->paginate($request->input('per_page', 12));

            return response()->json($propiedades);

        } catch (\Exception $e) {
            Log::error("Error al obtener propiedades por usuario ({$userId}): " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error del servidor al recuperar las propiedades del usuario.'], 500);
        }
    }

    /**
     * Muestra una propiedad específica con su tipo.
     */
    public function show($id)
    {
        $propiedad = Propiedad::with('tipoPropiedad')->find($id);

        if (!$propiedad) {
            return response()->json(['success' => false, 'message' => 'Propiedad no encontrada'], 404);
        }
        
        return response()->json(['success' => true, 'data' => $propiedad]);
    }

    /**
     * Crea una nueva propiedad.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'titulo'            => 'required|string|max:255',
            'descripcion'       => 'required|string',
            'direccion'         => 'required|string|max:255',
            'pais'              => 'required|string|max:100',
            'estado_ubicacion'  => 'required|string|max:100',
            'ciudad'            => 'required|string|max:100',
            'colonia'           => 'nullable|string|max:100',
            'precio'            => 'required|numeric|min:0',
            'habitaciones'      => 'required|integer|min:0',
            'banos'             => 'required|integer|min:0',
            'metros_cuadrados'  => 'required|integer|min:0',
            'amueblado'         => 'required|boolean',
            'anualizado'        => 'required|boolean',
            'mascotas'          => 'required|in:si,no',
            'tipo_propiedad_id' => 'required|exists:tipos_propiedad,id',
            'deposito'          => 'nullable|numeric',
            'email'             => 'required|email|max:255',
            'telefono'          => 'required|string|max:15',
            'fotos'             => 'required|array',
            'fotos.*'           => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        $photoPaths = [];
    if ($request->hasFile('fotos')) {
        foreach ($request->file('fotos') as $photo) {
            $path = $photo->store('propiedades', 'public');
            $photoPaths[] = $path;
        }
    }
    
    // Preparamos el array final
    $dataToSave = $validatedData;
    $dataToSave['fotos'] = $photoPaths; 
    $dataToSave['id_propietario'] = auth()->id();
    $dataToSave['estado_propiedad'] = 'Disponible';

    $propiedad = Propiedad::create($dataToSave);
    
        
        return response()->json([
            'success' => true,
            'message' => 'Propiedad publicada exitosamente',
            'data' => $propiedad
        ], 201);
    }

    /**
     * Actualiza una propiedad existente.
     */
    public function update(Request $request, $id)
    {
        try {
            $propiedad = Propiedad::findOrFail($id);

            if ($propiedad->id_propietario !== Auth::id()) {
                return response()->json(['success' => false, 'message' => 'No tienes permiso para actualizar esta propiedad'], 403);
            }

            $validatedData = $request->validate([
                // ... (todas tus validaciones de antes no cambian)
                'titulo'             => 'sometimes|required|string|max:255',
                // ...
                'fotos'              => 'sometimes|array',
                'fotos.*'            => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'existing_fotos'     => 'sometimes|json' // Validamos que el campo nuevo sea un JSON
            ]);

            // --- Lógica para manejar la actualización de fotos ---
            $currentPhotos = $propiedad->fotos ?? []; // Fotos actualmente en la BD
            $keptPhotos = json_decode($request->input('existing_fotos', '[]')); // Fotos que el usuario quiere conservar

            // 1. Determinar qué fotos borrar
            $photosToDelete = array_diff($currentPhotos, $keptPhotos);
            if (!empty($photosToDelete)) {
                Storage::disk('public')->delete($photosToDelete);
            }

            // 2. Subir las nuevas fotos
            $newPhotoPaths = [];
            if ($request->hasFile('fotos')) {
                foreach ($request->file('fotos') as $photo) {
                    $path = $photo->store('propiedades', 'public');
                    $newPhotoPaths[] = $path;
                }
            }

            // 3. Crear la lista final de fotos
            $finalPhotoList = array_merge($keptPhotos, $newPhotoPaths);
            $validatedData['fotos'] = $finalPhotoList;

            // Actualiza la propiedad con todos los datos
            $propiedad->update($validatedData);

            return response()->json([
                'success' => true,
                'data' => $propiedad->load('tipoPropiedad'),
                'message' => 'Propiedad actualizada exitosamente'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error en PropiedadController@update: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al actualizar la propiedad', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Elimina una propiedad.
     */
    public function destroy($id)
    {
        try {
            $propiedad = Propiedad::findOrFail($id);

            if ($propiedad->id_propietario !== Auth::id()) {
                return response()->json(['success' => false, 'message' => 'Acción no autorizada.'], 403);
            }

            if (is_array($propiedad->fotos)) {
                foreach ($propiedad->fotos as $photoPath) {
                    Storage::disk('public')->delete($photoPath);
                }
            }

            $propiedad->delete();

            return response()->json(['success' => true, 'message' => 'Propiedad eliminada exitosamente.'], 200);

        } catch (\Exception $e) {
            Log::error('Error en PropiedadController@destroy (ID: '.$id.'): ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error del servidor al eliminar la propiedad.'], 500);
        }
    }
}
