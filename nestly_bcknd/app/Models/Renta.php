<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Renta extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rentas';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'propiedad_id',
        'fecha_inicio',
        'fecha_fin',
        'monto',
        'deposito',
        'estado',
        'metodo_pago'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'monto' => 'decimal:2',
        'deposito' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the user that owns the renta.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the inquilino (user) that owns the renta.
     * Esto es un alias de la relación user() para mayor claridad
     */
    public function inquilino()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the propiedad that owns the renta.
     */
    public function propiedad()
    {
        return $this->belongsTo(Propiedad::class, 'propiedad_id', 'id_propiedad');
    }
}