<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\TipoPropiedad;

class Propiedad extends Model
{
    use HasFactory;

    protected $table = 'propiedades';
    protected $primaryKey = 'id_propiedad';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_propietario',
        'tipo_propiedad_id', 
        'titulo',
        'descripcion',
        'direccion',
        'pais',
        'estado_ubicacion',
        'ciudad',
        'colonia',
        'precio',
        'habitaciones',
        'banos',
        'metros_cuadrados',
        'amueblado',
        'anualizado',
        'deposito',
        'mascotas',
        'estado_propiedad',
        'fotos',
        'email',
        'telefono',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fotos' => 'array',
        'amueblado' => 'boolean',
        'anualizado' => 'boolean',
        'precio' => 'decimal:2',
        'deposito' => 'decimal:2',
    ];

    /**
     * Relación con el usuario propietario (Uno a Muchos Inversa).
     */
    public function propietario()
    {
        return $this->belongsTo(User::class, 'id_propietario');
    }

    /**
     * Relación con el tipo de propiedad (Uno a Muchos Inversa).
     */
    public function tipoPropiedad()
    {
        return $this->belongsTo(TipoPropiedad::class, 'tipo_propiedad_id');
    }

    public function resenas()
    {
        return $this->hasMany(Resena::class, 'propiedad_id', 'id_propiedad');
    }
    public function favoritos()
    {
        return $this->hasMany(Favoritos::class, 'propiedad_id', 'id_propiedad');
    }
}
