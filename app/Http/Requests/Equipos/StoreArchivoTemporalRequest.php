<?php

namespace App\Http\Requests\Equipos;

use Illuminate\Foundation\Http\FormRequest;

class StoreArchivoTemporalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'upload_token' => ['required', 'uuid'],
            'archivo' => ['required', 'file', 'max:10240'], // 10 MB
        ];
    }

    public function messages(): array
    {
        return [
            'upload_token.required' => 'El token de carga es obligatorio.',
            'upload_token.uuid' => 'El token de carga no es válido.',
            'archivo.required' => 'Debes seleccionar un archivo.',
            'archivo.file' => 'El archivo enviado no es válido.',
            'archivo.max' => 'El archivo no debe exceder 10 MB.',
        ];
    }
}