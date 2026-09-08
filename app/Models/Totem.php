<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Totem extends Model
{
    protected $fillable = [
        'codigo',
        'nombre',
        'ubicacion',
        'ip_autorizada',
        'serial',
        'version',
        'token',
        'token_hash',
        'auth_secret_hash',
        'token_expira_at',
        'activo',
        'ultimo_acceso',
        'ultimo_ping',
        'geolocalizacion_lat',
        'geolocalizacion_lng',
        'estado_operacional',
        'ultima_alerta_at',
        'ultima_alerta_mensaje',
        'metadata',
    ];

    protected $hidden = ['token', 'token_hash', 'auth_secret_hash'];

    protected $casts = [
        'activo' => 'boolean',
        'ultimo_acceso' => 'datetime',
        'ultimo_ping' => 'datetime',
        'token_expira_at' => 'datetime',
        'geolocalizacion_lat' => 'decimal:7',
        'geolocalizacion_lng' => 'decimal:7',
        'ultima_alerta_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function sesiones()
    {
        return $this->hasMany(TotemSesion::class);
    }

    public function ventas()
    {
        return $this->hasMany(TotemVenta::class);
    }

    public function logs()
    {
        return $this->hasMany(TotemLog::class);
    }
}
