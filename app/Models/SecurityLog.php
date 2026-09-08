<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityLog extends Model
{
    protected $fillable = [

        'user_id',
        'cliente_id',

        'accion',

        'modelo',
        'modelo_id',

        'ip',
        'device',

        'estado',

        'detalle'
    ];
}
