<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $fillable = [
        'nombre',
        'rut',
        'rut_hash',
        'telefono',
        'email',
        'tipo',
        'estado',
        'fecha_inscripcion',
    ];

    protected $hidden = ['rut', 'rut_hash'];

    protected $casts = [
        'fecha_inscripcion' => 'datetime',
    ];
}
