<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resena extends Model
{
    use HasFactory;

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'propiedad_id',
        'user_id',
        'puntuacion',
        'comentario',
    ];

    /**
     * Obtiene el usuario que escribió la reseña (la reseña Pertenece a un Usuario).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtiene la propiedad a la que pertenece la reseña (la reseña Pertenece a una Propiedad).
     */
    public function propiedad()
    {
        return $this->belongsTo(Propiedad::class, 'propiedad_id', 'id_propiedad');
    }
    // Agrega esta propiedad para que el contador de votos se añada automáticamente a las respuestas JSON
    protected $appends = ['votos_count'];

    /**
     * Define la relación de muchos a muchos con los usuarios que han votado.
     */
    public function votantes()
    {
        return $this->belongsToMany(User::class, 'resena_votos', 'resena_id', 'user_id');
    }

    /**
     * Un "accesor" para obtener el número de votos de forma sencilla.
     */
    public function getVotosCountAttribute()
    {
        return $this->votantes()->count();
    }
}