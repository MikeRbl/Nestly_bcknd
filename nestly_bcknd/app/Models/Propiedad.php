<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Models\User;

class Propiedad extends Model
{
    protected $table = 'propiedades';
    protected $primaryKey = 'id_propiedad';

    protected $fillable = [
        'id_propietario',
        'titulo',
        'descripcion',
        'direccion',
        'pais',
        'estado',
        'ciudad',
        'colonia',
        'precio',
        'habitaciones',
        'banos',
        'metros_cuadrados',
        'amueblado',
        'disponibilidad',
        'fotos',
        'email',
        'telefono',
        'apartamento',
        'casaPlaya',
        'industrial',
        'anualizado',
        'deposito',
        'mascotas',
        'tamano'
    ];

    protected $casts = [
        'fotos' => 'array', 
        'apartamento' => 'boolean',
        'casaPlaya' => 'boolean',
        'industrial' => 'boolean',
        'anualizado' => 'boolean',
        'amueblado' => 'boolean',
        'disponibilidad' => 'boolean',
        'precio' => 'decimal:2',
        'deposito' => 'decimal:2',
    ];

    // Relación con usuario propietario
    public function propietario()
    {
        return $this->belongsTo(User::class, 'id_propietario');
    }

}
