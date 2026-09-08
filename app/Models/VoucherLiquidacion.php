<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoucherLiquidacion extends Model
{
    use HasFactory;

    protected $table = 'voucher_liquidaciones';

    protected $fillable = [
        'voucher_rendicion_id',
        'profesional_id',
        'profesional_nombre',
        'banco',
        'tipo_cuenta',
        'numero_cuenta',
        'monto_profesional',
        'comision_veterchile',
        'estado',
        'medio_pago',
        'comprobante_transferencia',
        'pagado_en',
    ];

    public function rendicion()
    {
        return $this->belongsTo(
            \App\Models\VoucherRendicion::class,
            'voucher_rendicion_id'
        );
    }
}
