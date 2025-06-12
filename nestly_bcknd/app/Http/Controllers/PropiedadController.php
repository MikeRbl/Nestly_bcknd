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
     * Crea una nueva propiedad y guarda sus fotos.
     */
     public function store(Request $request)
    {
        // 1. Validamos los datos que SÍ existen en el nuevo formulario
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
            'tamano'            => 'nullable|string', 

            // Contacto y Fotos
            'email'             => 'required|email|max:255',
            'telefono'          => 'required|string|max:15',
            'fotos'             => 'required|array',
            'fotos.*'           => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        // 2. Procesamos las fotos
        $photoPaths = [];
        if ($request->hasFile('fotos')) {
            foreach ($request->file('fotos') as $photo) {
                // Guarda la foto en 'storage/app/public/propiedades' y obtiene la ruta
                $path = $photo->store('propiedades', 'public');
                $photoPaths[] = $path;
            }
        }
        
        // 3. Preparamos el array final para guardar en la base de datos
        $dataToSave = $validatedData;
        $dataToSave['fotos'] = json_encode($photoPaths); // Guardamos las rutas como un JSON
        $dataToSave['id_propietario'] = auth()->id(); // Asignamos el ID del usuario logueado
        $dataToSave['estado_propiedad'] = 'Disponible'; // Asignamos el estado por defecto

        // 4. Creamos la propiedad
        try {
            $propiedad = Propiedad::create($dataToSave);
        } catch (\Exception $e) {
            // Si algo sale mal, lo registramos en el log
            Log::error('Error al crear la propiedad: ' . $e->getMessage());
            return response()->json(['message' => 'Error interno al guardar la propiedad.'], 500);
        }
        
        // 5. Devolvemos una respuesta de éxito
        return response()->json([
            'success' => true,
            'message' => 'Propiedad publicada exitosamente',
            'data' => $propiedad
        ], 201);
    }

    /**
     * Muestra una propiedad específica.
     */
    public function show($id)
    {
        $propiedad = Propiedad::find($id);

        if (!$propiedad) {
            return response()->json(['success' => false, 'message' => 'Propiedad no encontrada'], 404);
        }

        // Eloquent automáticamente decodifica el campo 'fotos' a un array gracias al $casts.
        return response()->json(['success' => true, 'data' => $propiedad]);
    }

    /**
     * Actualiza una propiedad existente y sus fotos.
     */
    public function update(Request $request, $id)
    {
        // Se usa 'sometimes' para permitir actualizaciones parciales
        $validated = $request->validate([
            'titulo' => 'sometimes|required|string|max:255',
            'descripcion' => 'sometimes|required|string',
            'direccion' => 'sometimes|required|string|max:255',
            'pais' => 'sometimes|required|string|max:100',
            'estado' => 'sometimes|required|string|max:100',
            'ciudad' => 'sometimes|required|string|max:100',
            'colonia' => 'nullable|string|max:100',
            'precio' => 'sometimes|required|numeric',
            'habitaciones' => 'sometimes|required|integer|min:0',
            'banos' => 'sometimes|required|integer|min:0',
            'metros_cuadrados' => 'sometimes|required|integer|min:0',
            'amueblado' => 'sometimes|required|boolean',
            'disponibilidad' => 'sometimes|required|boolean',
            'email' => 'sometimes|required|email|max:255',
            'telefono' => 'sometimes|required|string|max:15',
            'apartamento' => 'sometimes|boolean',
            'casaPlaya' => 'sometimes|boolean',
            'industrial' => 'sometimes|boolean',
            'anualizado' => 'sometimes|boolean',
            'deposito' => 'sometimes|nullable|numeric',
            'mascotas' => 'sometimes|required|in:si,no',
            'tamano' => 'sometimes|nullable|integer',
            'fotos' => 'sometimes|nullable|array',
            'fotos.*' => 'image|mimes:jpg,jpeg,png,gif,svg|max:2048',
        ]);

        try {
            $propiedad = Propiedad::findOrFail($id);

            if ($propiedad->id_propietario !== Auth::id()) {
                return response()->json(['success' => false, 'message' => 'No tienes permiso para actualizar esta propiedad'], 403);
            }

            $updateData = $validated;

            if ($request->hasFile('fotos')) {
                // Borrar fotos antiguas para no dejar basura
                if (!empty($propiedad->fotos)) {
                    foreach ($propiedad->fotos as $oldPhotoPath) {
                        Storage::disk('public')->delete($oldPhotoPath);
                    }
                }
                // Subir las nuevas fotos
                $newPhotoPaths = [];
                foreach ($request->file('fotos') as $photo) {
                    $path = $photo->store('propiedades', 'public');
                    $newPhotoPaths[] = $path;
                }
                // Asignar el nuevo array de rutas
                $updateData['fotos'] = $newPhotoPaths;
            }

            $propiedad->update($updateData);

            return response()->json([
                'success' => true,
                'data' => $propiedad,
                'message' => 'Propiedad actualizada exitosamente'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Propiedad no encontrada para actualizar'], 404);
        } catch (\Exception $e) {
            Log::error('Error en PropiedadController@update: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al actualizar la propiedad', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Elimina una propiedad y sus fotos asociadas del storage.
     */
    public function destroy($id)
    {
        try {
            $propiedad = Propiedad::findOrFail($id);

            if ($propiedad->id_propietario !== Auth::id()) {
                return response()->json(['success' => false, 'message' => 'Acción no autorizada.'], 403);
            }

            // Borra las fotos del storage de forma segura
            if (!empty($propiedad->fotos)) {
                foreach ($propiedad->fotos as $photoUrl) {
                    // Limpia la ruta para asegurar que sea correcta para Storage::delete()
                    $path = str_replace('/storage/', '', parse_url($photoUrl, PHP_URL_PATH));
                    Storage::disk('public')->delete($path);
                }
            }

            $propiedad->delete();

            return response()->json(['success' => true, 'message' => 'Propiedad eliminada exitosamente.'], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Propiedad no encontrada.'], 404);
        } catch (\Exception $e) {
            Log::error('Error en PropiedadController@destroy (ID: '.$id.'): ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error del servidor al eliminar la propiedad.'], 500);
        }
    }

    /**
     * Lista todas las propiedades o las filtra por el ID del propietario.
     */
    public function index(Request $request)
    {
        try {
            $query = Propiedad::query();

            if ($request->has('id_propietario')) {
                $query->where('id_propietario', $request->query('id_propietario'));
            }

            $propiedades = $query->orderBy('created_at', 'desc')->get();

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
    public function indexByUser($userId)
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Usuario no encontrado.'], 404);
            }
            
            $propiedades = Propiedad::where('id_propietario', $userId)
                                    ->orderBy('created_at', 'desc')
                                    ->get();

            if ($propiedades->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'Este usuario no tiene propiedades registradas.'
                ], 200);
            }

            return response()->json([
                'success' => true,
                'data' => $propiedades,
                'message' => 'Propiedades del usuario recuperadas exitosamente.'
            ], 200);

        } catch (\Exception $e) {
            Log::error("Error al obtener propiedades por usuario ({$userId}): " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error del servidor al recuperar las propiedades del usuario.'], 500);
        }
    }
}