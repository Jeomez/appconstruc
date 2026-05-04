<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToEmpresa;

class operador extends Model
{
    use BelongsToEmpresa;
    
    protected $fillable = ['nombre', 'activo'];
}
