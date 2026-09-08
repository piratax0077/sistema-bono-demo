<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campana extends Model
{
    protected $table = 'campanas';

    protected $fillable = [
        'nombre',
        'descripcion',
        'imagen',
        'activa',
        'fecha_inicio',
        'fecha_fin'
    ];
}
