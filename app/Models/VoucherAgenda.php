<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherAgenda extends Model
{
    protected $table = 'voucher_agendas';

    protected $fillable = [
        'voucher_id',
        'cliente_id',
        'mascota_id',
        'profesional_id',
        'centro_atencion_id',
        'fecha_hora_solicitada',
        'fecha_hora_confirmada',
        'estado',
        'observacion',
        'medichile_hora_medica_id',
        'medichile_estado_id',
        'medichile_sincronizado_at',
        'medichile_sync_error',
    ];

    protected $dates = [
        'fecha_hora_solicitada',
        'fecha_hora_confirmada',
        'medichile_sincronizado_at',
    ];

    protected $casts = [
        'fecha_hora_solicitada' => 'datetime',
        'fecha_hora_confirmada' => 'datetime',
        'medichile_sincronizado_at' => 'datetime',
        'medichile_hora_medica_id' => 'integer',
        'medichile_estado_id' => 'integer',
    ];

    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
    }

    public function profesional()
    {
        return $this->belongsTo(VoucherProfesional::class, 'profesional_id');
    }
}
