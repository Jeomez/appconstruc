<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory;

    protected $table = 'empresas';

    protected $fillable = [
        'nombre',
        'direccion',
        'cp',
        'rfc',
        'telefono',
        'activo',
    ];

    public function users()
    {
        return $this->hasMany(\App\Models\User::class);
    }
}
