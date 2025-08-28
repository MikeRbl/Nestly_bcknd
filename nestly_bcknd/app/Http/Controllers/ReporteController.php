<?php

namespace App\Http\Controllers;

use App\Models\Reporte;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Propiedad;
use App\Models\Resena;

class ReporteController extends Controller
{
    // Listar reportes con filtros y paginación
    public function index(Request $request)
    {
        $this->authorize('viewAny', Reporte::class);

        $query = Reporte::with(['reportador', 'reportable']);

        // Filtro por estado (pendiente, descartado, resuelto)
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $query->when($request->reportable_type, function ($q, $type) {
            // Construye el namespace completo del modelo
            $modelType = 'App\\Models\\' . $type;
            $q->where('reportable_type', $modelType);
        });
        
        // Filtro por motivo
        if ($request->filled('motivo')) {
            $query->where('motivo', 'like', '%'.$request->motivo.'%');
        }

        // Ordenar por fecha más reciente
        $reportes = $query->latest()->paginate($request->query('per_page', 10));

        return response()->json($reportes);
    }

    // Cambiar estado de un reporte (descartar, resuelto, pendiente)
    public function actualizarEstado(Request $request, Reporte $reporte)
    {
        $this->authorize('update', $reporte);

        $validated = $request->validate([
            'estado' => 'required|in:pendiente,descartado,resuelto',
        ]);

        $reporte->estado = $validated['estado'];
        $reporte->save();

        return response()->json(['message' => 'Estado actualizado', 'reporte' => $reporte]);
    }

    // Suspender usuario reportado (si el reporte es de tipo usuario)
    public function suspenderUsuarioReportado(Reporte $reporte)
    {
        $this->authorize('update', $reporte);

        if (! $reporte->reportable instanceof User) {
            return response()->json(['message' => 'El reporte no es sobre un usuario'], 400);
        }

        $usuario = $reporte->reportable;

        $usuario->status = 'baneado';
        $usuario->save();

        $reporte->estado = 'resuelto';
        $reporte->save();

        return response()->json([
            'message' => 'Usuario suspendido y reporte resuelto',
            'usuario' => $usuario,
            'reporte' => $reporte
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate([
            
            'reportable_type' => 'required|string|in:App\Models\User,App\Models\Propiedad,App\Models\Resena,App\Models\Testimonio',
            'reportable_id' => 'required|integer',
            'motivo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        $modelClass = $validated['reportable_type'];
        $reportable = $modelClass::find($validated['reportable_id']);

        if (!$reportable) {
            return response()->json(['message' => 'Recurso reportado no encontrado'], 404);
        }

        $reporte = Reporte::create([
            'reportador_id' => $user->id,
            'reportable_type' => $validated['reportable_type'],
            'reportable_id' => $validated['reportable_id'],
            'motivo' => $validated['motivo'],
            'descripcion' => $validated['descripcion'] ?? null,
            'estado' => 'pendiente',
        ]);

        return response()->json(['message' => 'Reporte creado con éxito', 'reporte' => $reporte], 201);
    }

    // Métodos para eliminar propiedad o reseña reportada
    public function eliminarPropiedadReportada(Reporte $reporte)
    {
        $this->authorize('update', $reporte);

        if (! $reporte->reportable instanceof \App\Models\Propiedad) {
            return response()->json(['message' => 'El reporte no es sobre una propiedad'], 400);
        }

        $propiedad = $reporte->reportable;
        $propiedad->delete();

        $reporte->estado = 'resuelto';
        $reporte->save();

        return response()->json(['message' => 'Propiedad eliminada y reporte resuelto', 'reporte' => $reporte]);
    }

    public function eliminarResenaReportada(Reporte $reporte)
    {
        $this->authorize('update', $reporte);

        if (! $reporte->reportable instanceof \App\Models\Resena) {
            return response()->json(['message' => 'El reporte no es sobre una reseña'], 400);
        }

        $resena = $reporte->reportable;
        $resena->delete();

        $reporte->estado = 'resuelto';
        $reporte->save();

        return response()->json(['message' => 'Reseña eliminada y reporte resuelto', 'reporte' => $reporte]);
    }
}
