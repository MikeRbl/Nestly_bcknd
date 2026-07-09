<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Esta línea comprueba si el usuario autenticado tiene el permiso 'create' 
        // definido en tu UserPolicy. Es la forma correcta y segura de hacerlo.
        return $this->user()->can('create', User::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        // Aquí movemos todas las reglas de validación que estaban en el método 'store'
        // del UserController.
        return [
            'first_name' => 'required|string|max:255',
            'last_name_paternal' => 'required|string|max:255',
            'last_name_maternal' => 'required|string|max:255',
            'phone' => ['required', 'string', 'regex:/^[0-9]{10}$/'],
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
            'role' => 'sometimes|string|in:admin,propietario,inquilino',
        ];
    }
}
