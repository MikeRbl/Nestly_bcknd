<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Storage; // Para manejar archivos

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
        'avatar', // Asegúrate de que esté en fillable
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Relación con Propiedades
    public function propiedades()
    {
        return $this->hasMany(Propiedad::class, 'id_propietario', 'id');
    }

    // Accesor para el nombre completo
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name_paternal} {$this->last_name_maternal}";
    }

    // Accesor para la URL del avatar (¡Nuevo!)
    public function getAvatarUrlAttribute()
    {
        return $this->avatar 
            ? asset('storage/avatars/' . $this->avatar) 
            : asset('images/default-avatar.jpg'); // Ruta de imagen por defecto
    }

    // Método para eliminar el avatar al borrar el usuario (¡Nuevo!)
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($user) {
            if ($user->avatar) {
                Storage::delete('public/avatars/' . $user->avatar);
            }
        });
    }
}