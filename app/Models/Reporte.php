<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reporte extends Model 
{
    use HasFactory;

    protected $fillable = [
        'reportador_id',
        'reportable_id',
        'reportable_type',
        'motivo',
        'descripcion',
        'estado',
    ];

    /**
     * Usuario que hizo el reporte
     */
    public function reportador()
{
    return $this->belongsTo(User::class, 'reportador_id');
}

    /**
     * Relación polimórfica al recurso reportado (usuario, propiedad o reseña)
     */
    public function reportable(): MorphTo
    {
        return $this->morphTo();
    }

}
