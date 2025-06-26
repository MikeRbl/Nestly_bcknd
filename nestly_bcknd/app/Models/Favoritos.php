<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Favoritos extends Model
{
    use HasFactory;

    /**
     * El nombre de la tabla asociada con el modelo.
     */
    protected $table = 'favoritos';

   
    protected $fillable = [
        'user_id',
        'propiedad_id',
    ];

    /**
     * Define la relación inversa: Un favorito pertenece a un usuario.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Define la relación inversa: Un favorito pertenece a una propiedad.
     * Se especifican las claves para que coincidan con tu base de datos.
     */
    public function propiedad()
    {
        return $this->belongsTo(Propiedad::class, 'propiedad_id', 'id_propiedad');
    }
}
