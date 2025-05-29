<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Storage;

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
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected $appends = [
        'full_name',
        'avatar_url'
    ];

    // Relación con Propiedades
    public function propiedades()
    {
        return $this->hasMany(Propiedad::class, 'id_propietario');
    }

    // Accesor para el nombre completo
    public function getFullNameAttribute()
    {
        $names = [
            $this->first_name,
            $this->last_name_paternal,
            $this->last_name_maternal
        ];
        
        return implode(' ', array_filter($names));
    }

    // Accesor para la URL del avatar
    public function getAvatarUrlAttribute()
    {
        if (!$this->avatar) {
            return asset('images/default-avatar.jpg');
        }
        
        // Verifica si ya es una URL completa (por si viene de redes sociales)
        if (filter_var($this->avatar, FILTER_VALIDATE_URL)) {
            return $this->avatar;
        }
        
        return Storage::exists('public/avatars/'.$this->avatar) 
            ? asset('storage/avatars/'.$this->avatar)
            : asset('images/default-avatar.jpg');
    }

    // Método para eliminar el avatar
    public function deleteAvatar()
    {
        if ($this->avatar && Storage::exists('public/avatars/'.$this->avatar)) {
            Storage::delete('public/avatars/'.$this->avatar);
            $this->avatar = null;
            $this->save();
        }
    }

    // Eventos del modelo
    protected static function boot()
    {
        parent::boot();

        // Eliminar avatar cuando se elimina el usuario
        static::deleting(function ($user) {
            $user->deleteAvatar();
        });

        // Limpiar datos antes de guardar
        static::saving(function ($user) {
            $user->email = strtolower(trim($user->email));
            $user->first_name = ucfirst(trim($user->first_name));
            $user->last_name_paternal = ucfirst(trim($user->last_name_paternal));
            $user->last_name_maternal = ucfirst(trim($user->last_name_maternal));
        });
    }
}