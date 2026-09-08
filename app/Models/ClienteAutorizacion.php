<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClienteAutorizacion extends Model
{
    protected $table = 'cliente_autorizaciones';

    protected $fillable = [
        'cliente_id',
        'dispositivo_id',
        'tipo_accion',
        'referencia_tipo',
        'referencia_id',
        'token',
        'estado',
        'ip_solicitante',
        'metadata',
        'expira_at',
        'aprobada_at',
        'rechazada_at',
    ];

    protected $dates = [
        'expira_at',
        'aprobada_at',
        'rechazada_at',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];
}
