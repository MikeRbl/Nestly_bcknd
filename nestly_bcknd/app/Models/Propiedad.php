<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Propiedad extends Model
{
    protected $table = 'propiedades';
    protected $primaryKey = 'id_propiedad'; 

    protected $fillable = [
        'id_propietario',
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
        'fotos'
    ];

    public function propietario()
    {
        return $this->belongsTo(User::class, 'id_propietario');
    }
}