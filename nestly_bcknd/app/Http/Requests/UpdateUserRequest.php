<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Primero, obtenemos el usuario que se está intentando actualizar desde la ruta.
        $userToUpdate = $this->route('user');

        // Luego, verificamos si el usuario autenticado tiene permiso para actualizar ese perfil.
        return $this->user()->can('update', $userToUpdate);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        // Obtenemos el usuario del parámetro de la ruta para poder ignorar su propio email.
        $user = $this->route('user');

        return [
            // 'sometimes' significa que el campo solo se valida si está presente en la petición.
            'first_name' => 'sometimes|string|max:255',
            'last_name_paternal' => 'sometimes|string|max:255',
            'last_name_maternal' => 'sometimes|string|max:255',
            'phone' => ['sometimes', 'string', 'regex:/^[0-9]{10}$/'],
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            
            'role' => 'sometimes|string|in:admin,propietario,inquilino',
        ];
    }
}
