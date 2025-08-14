<?php

namespace App\Http\Controllers\Api;



use App\Http\Controllers\Controller;
use App\Models\Renta;
use App\Models\Propiedad;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class RentaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtiene solo las rentas del usuario autenticado
        return Renta::with(['user', 'propiedad'])
            ->where('user_id', auth()->id())
            ->orderBy('fecha_inicio', 'desc')
            ->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    \Log::debug('Datos recibidos:', $request->all());
    
    $propiedad = Propiedad::find($request->propiedad_id);
    \Log::debug('Estado de propiedad:', [
        'id' => $propiedad->id_propiedad,
        'estado' => $propiedad->estado_propiedad,
        'existe' => (bool)$propiedad
    ]);
    // Captura el request en una variable que será accesible en los closures
    $requestData = $request;
    
    $validated = $request->validate([
        'propiedad_id' => [
            'required',
            'exists:propiedades,id_propiedad',
            function ($attribute, $value, $fail) use ($requestData) {  // Usa $requestData aquí
                $propiedad = Propiedad::find($value);
                
                if (!$propiedad) {
                    $fail('La propiedad no existe.');
                    return;
                }
                
                if ($propiedad->estado_propiedad !== 'Disponible') {
                    $fail('La propiedad no está disponible para renta.');
                }
                
                // Verificar conflictos de fechas
                $conflicto = Renta::where('propiedad_id', $value)
                    ->where('estado', 'activa')
                    ->where(function($query) use ($requestData) {  // Usa $requestData aquí
                        $query->whereBetween('fecha_inicio', [$requestData->fecha_inicio, $requestData->fecha_fin])
                              ->orWhereBetween('fecha_fin', [$requestData->fecha_inicio, $requestData->fecha_fin])
                              ->orWhere(function($q) use ($requestData) {
                                  $q->where('fecha_inicio', '<', $requestData->fecha_inicio)
                                    ->where('fecha_fin', '>', $requestData->fecha_fin);
                              });
                    })
                    ->exists();
                    
                if ($conflicto) {
                    $fail('La propiedad ya tiene una renta activa en las fechas seleccionadas.');
                }
            }
        ],
        'fecha_inicio' => 'required|date|after_or_equal:today',
        'fecha_fin' => 'required|date|after:fecha_inicio',
        'monto' => [
            'required',
            'numeric',
            'min:0',
            function ($attribute, $value, $fail) use ($requestData) {  // Usa $requestData aquí
                $propiedad = Propiedad::find($requestData->propiedad_id);
                if ($propiedad && $value < $propiedad->precio) {
                    $fail('El monto no puede ser menor al precio base de la propiedad.');
                }
            }
        ],
        'deposito' => [
            'nullable',
            'numeric',
            'min:0',
            function ($attribute, $value, $fail) use ($requestData) {  // Usa $requestData aquí
                $propiedad = Propiedad::find($requestData->propiedad_id);
                if ($propiedad && $value > $propiedad->deposito) {
                    $fail('El depósito no puede ser mayor al requerido por la propiedad.');
                }
            }
        ],
        'metodo_pago' => 'required|string|in:transferencia,tarjeta,efectivo'
    ]);

    // Asignar valores automáticos
    $validated['user_id'] = auth()->id();
    $validated['estado'] = 'activa';

    // Usar transacción para asegurar consistencia
    return DB::transaction(function () use ($validated) {
        $renta = Renta::create($validated);
        
        // Actualizar estado de la propiedad
        Propiedad::where('id_propiedad', $validated['propiedad_id'])
            ->update(['estado_propiedad' => 'Rentada']);
        
        return response()->json($renta->load('propiedad'), 201);
    });
}

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $renta = Renta::with(['user', 'propiedad'])
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();
            
        return response()->json($renta);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $renta = Renta::where('user_id', auth()->id())
            ->findOrFail($id);
            
        // Solo permitir actualizar ciertos campos
        $validated = $request->validate([
            'fecha_fin' => 'sometimes|date|after:fecha_inicio',
            'metodo_pago' => 'sometimes|string|in:transferencia,tarjeta,efectivo',
            'estado' => [
                'sometimes',
                'string',
                Rule::in(['activa', 'completada', 'cancelada']),
                function ($attribute, $value, $fail) use ($renta) {
                    if ($value === 'cancelada' && $renta->fecha_inicio < now()) {
                        $fail('No puedes cancelar una renta que ya comenzó.');
                    }
                }
            ]
        ]);
        
        // Actualizar y devolver respuesta
        $renta->update($validated);
        
        // Si se cancela o completa, liberar propiedad
        if (in_array($validated['estado'] ?? null, ['completada', 'cancelada'])) {
            Propiedad::where('id_propiedad', $renta->propiedad_id)
                ->update(['estado_propiedad' => 'Disponible']);
        }
        
        return response()->json($renta->fresh()->load('propiedad'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $renta = Renta::where('user_id', auth()->id())
            ->findOrFail($id);
            
        // Solo permitir cancelar si no ha comenzado
        if ($renta->fecha_inicio < now()) {
            return response()->json([
                'message' => 'No puedes eliminar una renta que ya comenzó'
            ], 422);
        }
        
        DB::transaction(function () use ($renta) {
            // Liberar propiedad primero
            Propiedad::where('id_propiedad', $renta->propiedad_id)
                ->update(['estado_propiedad' => 'Disponible']);
                
            $renta->delete();
        });
        
        return response()->noContent();
    }

    /**
     * Obtiene las rentas de un usuario específico
     */
   public function rentasPorUsuario($userId)
{
    try {
        if (auth()->id() != $userId) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        // Carga solo la propiedad sin las fotos
        $rentas = Renta::with('propiedad')
            ->where('user_id', $userId)
            ->orderBy('fecha_inicio', 'desc')
            ->get();

        return response()->json($rentas);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Error al obtener las rentas',
            'error' => $e->getMessage()
        ], 500);
    }
}
    /**
     * Obtiene el historial de rentas de una propiedad
     */
    public function rentasPorPropiedad($propiedadId)
    {
        $this->authorize('viewAny', Renta::class);
        
        return Renta::with('user')
            ->where('propiedad_id', $propiedadId)
            ->orderBy('fecha_inicio', 'desc')
            ->get();
    }
}