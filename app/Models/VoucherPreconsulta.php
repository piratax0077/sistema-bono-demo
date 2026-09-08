<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherPreconsulta extends Model
{
    protected $table = 'voucher_preconsultas';

    protected $fillable = [
        'external_consulta_id', 'usuario_id', 'dependiente_id',
        'profesional_id', 'laboratorio_id', 'servicio_id',
        'relacion_autorizada_id', 'voucher_id', 'usuario_consulta_tipo',
        'usuario_rut_hash', 'usuario_rut_sha256', 'prestador_tipo',
        'prestador_rut_hash', 'prestador_rut_sha256',
        'hash_validacion_modo', 'geolocalizacion_lat',
        'geolocalizacion_lng', 'fecha_consulta',
        'hora_respuesta', 'token_hash', 'token_expira_at',
        'token_consumido_at', 'resultado', 'motivo',
        'codigo_voucher_externo', 'voucher_generado_codigo',
        'guardar_detalle', 'request_fingerprint', 'request_payload',
        'response_payload', 'ip', 'user_agent_hash',
    ];

    protected $casts = [
        'fecha_consulta' => 'datetime',
        'hora_respuesta' => 'datetime',
        'token_expira_at' => 'datetime',
        'token_consumido_at' => 'datetime',
        'guardar_detalle' => 'boolean',
        'request_payload' => 'array',
        'response_payload' => 'array',
    ];

    public function usuario()
    {
        return $this->belongsTo(VoucherBaseUsuario::class, 'usuario_id');
    }

    public function dependiente()
    {
        return $this->belongsTo(VoucherBaseDependiente::class, 'dependiente_id');
    }

    public function profesional()
    {
        return $this->belongsTo(VoucherBaseProfesional::class, 'profesional_id');
    }

    public function laboratorio()
    {
        return $this->belongsTo(VoucherBaseLaboratorio::class, 'laboratorio_id');
    }

    public function servicio()
    {
        return $this->belongsTo(VoucherBaseServicio::class, 'servicio_id');
    }

    public function relacionAutorizada()
    {
        return $this->belongsTo(VoucherBaseRelacion::class, 'relacion_autorizada_id');
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
    }

    public function auditoria()
    {
        return $this->hasOne(VoucherPreconsultaAuditoria::class, 'preconsulta_id');
    }
}
