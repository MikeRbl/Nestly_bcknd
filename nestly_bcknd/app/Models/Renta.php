<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Renta extends Model
{
    use HasFactory;

    protected $fillable = [
        'propiedad_id',
        'inquilino_id',
        'propietario_id',
        'fecha_inicio',
        'fecha_fin',
        'monto_mensual',
        'estado',
    ];

    /**
     * Una renta pertenece a una propiedad.
     */
    public function propiedad(): BelongsTo
    {
        return $this->belongsTo(Propiedad::class);
    }

    /**
     * Una renta pertenece a un inquilino (un usuario).
     */
    public function inquilino(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inquilino_id');
    }

    /**
     * Una renta pertenece a un propietario (un usuario).
     */
    public function propietario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'propietario_id');
    }
}
