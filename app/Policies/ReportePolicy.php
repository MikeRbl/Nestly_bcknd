<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Reporte;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReportePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        // Solo admins pueden listar reportes
        return $user->role === 'admin';
    }

    public function view(User $user, Reporte $reporte)
    {
        // Solo admins pueden ver reportes específicos
        return $user->role === 'admin';
    }

    public function create(User $user)
    {
        // En general, creación la puedes limitar según necesidad
        return $user->role === 'admin';
    }

    public function update(User $user, Reporte $reporte)
    {
        // Solo admins pueden actualizar reportes
        return $user->role === 'admin';
    }

    public function delete(User $user, Reporte $reporte)
    {
        // Solo admins pueden borrar reportes
        return $user->role === 'admin';
    }

    public function updateEstado(User $user, Reporte $reporte)
    {
        // Método custom para actualizar el estado (descartar, resolver)
        return $user->role === 'admin';
    }
}
