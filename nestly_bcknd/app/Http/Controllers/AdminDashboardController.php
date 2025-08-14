<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Propiedad;
use App\Models\Renta;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    /**
     * Devuelve los datos para el dashboard del administrador.
     */
    public function getDashboardData(): JsonResponse
    {
        // Estadísticas principales
        $totalUsers = User::count();
        $activeProperties = Propiedad::where('estado_propiedad', 'Disponible')->count();
        $currentRents = Renta::where('estado', 'activa')->count();
        $monthlyIncome = Renta::where('estado', 'activa')->sum('monto_mensual');

        // Datos para gráfico de usuarios últimos 6 meses
        $userChartData = User::select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        // Formatear labels con meses en español
        $chartLabels = $userChartData->map(function ($item) {
            return Carbon::createFromDate($item->year, $item->month, 1)
                ->locale('es')
                ->isoFormat('MMMM');
        });

        $chartValues = $userChartData->pluck('count');

        // Últimas actividades: usuarios y propiedades
        $recentUsers = User::latest()->take(3)->get()->map(function ($user) {
            return [
                'type' => 'Nuevo Usuario',
                'message' => "El usuario {$user->first_name} se registró.",
                'timestamp' => $user->created_at
            ];
        });

        $recentProperties = Propiedad::with('propietario')->latest()->take(3)->get()->map(function ($propiedad) {
            return [
                'type' => 'Nueva Propiedad',
                'message' => "{$propiedad->propietario->first_name} publicó \"{$propiedad->titulo}\".",
                'timestamp' => $propiedad->created_at
            ];
        });

        // Combinar, ordenar y tomar solo 5 actividades más recientes
        $recentActivities = $recentUsers
            ->concat($recentProperties)
            ->sortByDesc('timestamp')
            ->take(5)
            ->values();

        // Respuesta JSON con código HTTP 200 OK
        return response()->json([
            'stats' => [
                'total_users' => $totalUsers,
                'active_properties' => $activeProperties,
                'current_rents' => $currentRents,
                'monthly_income' => $monthlyIncome,
            ],
            'user_chart' => [
                'labels' => $chartLabels,
                'values' => $chartValues,
            ],
            'recent_activities' => $recentActivities,
        ], 200);
    }
}
