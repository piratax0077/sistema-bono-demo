<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherAtencion extends Model
{
    protected $table = 'voucher_atenciones';

    protected $fillable = [
        'voucher_id',
        'agenda_id',
        'cliente_id',
        'mascota_id',
        'profesional_id',
        'asistente_id',
        'inicio_atencion',
        'fin_atencion',
        'cerrada_at',
        'validada_at',
        'ip_profesional',
        'ip_asistente',
        'user_agent_profesional',
        'user_agent_asistente',
        'lat',
        'lng',
        'direccion',
        'estado',
        'riesgo',
        'diagnostico',
        'hash_auditoria',
        'observacion',
    ];

    protected $dates = [
        'inicio_atencion',
        'fin_atencion',
        'cerrada_at',
        'validada_at',
    ];

    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
    }

    public function agenda()
    {
        return $this->belongsTo(VoucherAgenda::class, 'agenda_id');
    }

}
