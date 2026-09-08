<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClienteDispositivo extends Model
{
    protected $table = 'cliente_dispositivos';

    protected $fillable = [
        'cliente_id',
        'imei_hash',
        'device_token',
        'nombre_dispositivo',
        'estado',
        'ultimo_uso_at',
        'ip_registro',
    ];
}
