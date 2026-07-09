<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name_paternal',
        'last_name_maternal',
        'phone',
        'role',
        'status',
        'email',
        'password',
        'avatar',
        'suspension_ends_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'suspension_ends_at' => 'datetime',
    ];

    protected $appends = [
        'full_name',
        'avatar_url',
        'profile_complete'
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
                
                return asset('storage/avatars/default-profile.png');
            }
            
            return asset('storage/avatars/'.$this->avatar);
        }

    // Método para eliminar el avatar
 public function deleteAvatar()
{
    if ($this->avatar) {
        Storage::disk('public')->delete('avatars/'.$this->avatar);
        $this->avatar = null;
        $this->save(); // <-- El save() es necesario aquí ahora
    }
}

    // Eventos del modelo
   protected static function boot()
{
    parent::boot();

    // static::deleting(...); // <-- ELIMINA O COMENTA TODO ESTE BLOQUE

    // El evento 'saving' se queda como está
    static::saving(function ($user) {
        $user->email = strtolower(trim($user->email));
        $user->first_name = ucfirst(trim($user->first_name));
        $user->last_name_paternal = ucfirst(trim($user->last_name_paternal));
        $user->last_name_maternal = ucfirst(trim($user->last_name_maternal));
    });
}
    // Dentro de la clase User
    public function resenasVotadas()
    {
        return $this->belongsToMany(Resena::class, 'resena_votos', 'user_id', 'resena_id');
    }
    public function favoritos()
    {
        return $this->hasMany(Favoritos::class, 'user_id', 'id');
    }

    public function rentas()
    {
        return $this->hasMany(Renta::class);
    }
    public function roleRequests(): HasMany
    {
        return $this->hasMany(RoleRequest::class);
    }
    protected function profileComplete(): Attribute
    {
        return Attribute::make(
            get: fn () =>
                !empty($this->first_name) &&
                !empty($this->last_name_paternal) &&
                !empty($this->phone) &&
                !empty($this->avatar)
        );
    }
}