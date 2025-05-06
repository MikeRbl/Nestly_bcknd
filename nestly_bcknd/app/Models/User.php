<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name_paternal',
        'last_name_maternal',
        'phone',
        'role',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Relación con Propiedades (un usuario puede tener muchas propiedades)
    public function propiedades()
    {
        return $this->hasMany(Propiedad::class, 'id_propietario', 'id');
    }

    // Accesor para el nombre completo
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name_paternal} {$this->last_name_maternal}";
    }
}