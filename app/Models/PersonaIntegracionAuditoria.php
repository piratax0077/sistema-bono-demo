<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonaIntegracionAuditoria extends Model
{
    protected $table = 'persona_integracion_auditorias';

    protected $fillable = [
        'operacion', 'rut_hash', 'resultado', 'http_status', 'duracion_ms',
        'correlation_id', 'user_id', 'totem_id', 'ip', 'detalle',
    ];
}
