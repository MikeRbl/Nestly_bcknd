<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Propiedad;
use App\Models\Renta;
use App\Models\RoleRequest;
use App\Models\Reporte;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    /**
     * Obtiene todos los datos necesarios para el dashboard del administrador.
     */
    public function getDashboardData(Request $request): JsonResponse
    {
        // 1. Calcula el ingreso mensual y guárdalo en una variable
        $monthlyIncome = Renta::where('estado', 'activa')
                              ->whereMonth('fecha_inicio', now()->month)
                              ->sum('monto');

        $stats = [
            'total_users' => User::count(),
            'active_properties' => Propiedad::where('estado_propiedad', 'Disponible')->count(),
            'current_rents' => Renta::where('estado', 'activa')->count(),
            // 2. Usa la variable aquí
            'monthly_income' => $monthlyIncome,
            // 3. Y úsala de nuevo aquí para calcular las ganancias
            'monthly_earnings' => $monthlyIncome * 0.10,
            'pending_role_requests' => RoleRequest::where('status', 'pendiente')->count(),
            'unresolved_reports' => Reporte::where('estado', 'pendiente')->count(),
        ];

        // --- OBTENCIÓN DE ACTIVIDAD RECIENTE ---
        $recentUsers = User::latest()->take(3)->get()->map(function ($user) {
            return [
                'type' => 'Nuevo Usuario',
                'message' => "El usuario {$user->first_name} se registró.",
                'timestamp' => $user->created_at
            ];
        });

        $recentProperties = Propiedad::with('propietario')->latest()->take(3)->get()->map(function ($propiedad) {
            // Asegurarse de que el propietario existe para evitar errores
            $ownerName = $propiedad->propietario ? $propiedad->propietario->first_name : 'un propietario';
            return [
                'type' => 'Nueva Propiedad',
                'message' => "{$ownerName} publicó \"{$propiedad->titulo}\".",
                'timestamp' => $propiedad->created_at
            ];
        });

        // Combinar, ordenar por fecha y tomar las 5 actividades más recientes
        $recentActivities = $recentUsers
            ->concat($recentProperties)
            ->sortByDesc('timestamp')
            ->take(5)
            ->values();

        // --- RESPUESTA FINAL ---
        return response()->json([
            'stats' => $stats,
            'recent_activities' => $recentActivities,
        ], 200);
    }
}
