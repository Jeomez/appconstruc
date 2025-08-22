<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CargaIndexRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'mes'        => ['nullable','regex:/^\d{6}$/'], // YYYYMM
            'id_equipo'  => ['nullable','integer','min:1'],
            'id_obra'    => ['nullable','integer','min:1'],
            'limit'      => ['nullable','integer','min:1','max:200'], // tamaño de página
            'cursor'     => ['nullable','string'], // cursor pagination
            'sort'       => ['nullable','in:fecha_desc,fecha_asc,folio_desc,folio_asc'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
