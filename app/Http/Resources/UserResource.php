<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // 'toArray' define la estructura del JSON que se enviará.
        // Es como una plantilla para tus respuestas de usuario.
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name_paternal' => $this->last_name_paternal,
            'last_name_maternal' => $this->last_name_maternal,
            'phone' => $this->phone,
            'email' => $this->email,
            'role' => $this->role,
            'status' => $this->status,
            
           
            'full_name' => $this->getFullNameAttribute(),
            'avatar_url' => $this->getAvatarUrlAttribute(),
            'profile_complete' => $this->getProfileCompleteAttribute(),

            // Formateamos las fechas para mantener un estándar.
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
