<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditorNotificacion extends Model
{
    protected $table = 'auditor_notificaciones';

    protected $fillable = [
        'voucher_id',
        'alerta_id',
        'titulo',
        'mensaje',
        'leido',
        'fecha_lectura',
    ];

    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
    }

    public function alerta()
    {
        return $this->belongsTo(VoucherAlerta::class, 'alerta_id');
    }
}
