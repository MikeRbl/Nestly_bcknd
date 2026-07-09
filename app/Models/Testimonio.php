<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Testimonio extends Model
{
    use HasFactory;

    protected $table = 'testimonios';

    protected $fillable = [
        'id_usuario',
        'nombre',
        'comentario',
        'puntuacion',
        'avatar',
        'fecha',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    // Relación con el usuario que lo creó
    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
