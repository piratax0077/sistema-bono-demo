<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagoAutorizacion extends Model
{
    protected $table = 'pago_autorizaciones';

    protected $fillable = [
        'voucher_liquidacion_id',
        'voucher_rendicion_id',
        'profesional_id',
        'admin_id',
        'token',
        'estado',
        'ip_solicitud',
        'ip_respuesta',
        'device_id',
        'expira_at',
        'aprobada_at',
        'rechazada_at',
    ];

    protected $dates = [
        'expira_at',
        'aprobada_at',
        'rechazada_at',
    ];

    public function liquidacion()
    {
        return $this->belongsTo(VoucherLiquidacion::class, 'voucher_liquidacion_id');
    }

    public function rendicion()
    {
        return $this->belongsTo(VoucherRendicion::class, 'voucher_rendicion_id');
    }
}
