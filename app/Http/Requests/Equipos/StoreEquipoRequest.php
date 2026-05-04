<?php

namespace App\Http\Requests\Equipos;

use Illuminate\Foundation\Http\FormRequest;

class StoreEquipoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tipo' => ['required'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'upload_token' => ['nullable', 'uuid'],
        ];
    }
}