<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoucherCobro extends Model
{
    use HasFactory;
        protected $fillable = [
            'voucher_id',
            'profesional_id',
            'voucher_rendicion_id',
            'auditor_id',
            'veterinario_id',
            'veterinario_nombre',
            'sucursal',
            'monto_cobrado',
            'estado',
            'cobrado_en',
            'auditado_at',
            'observacion_auditor',
            'resultado_controles',
            'hash_visto_bueno',
            'pago_estado',
            'pago_decidido_por',
            'pago_decidido_at',
            'pago_objecion',
            'deposito_comprobante',
        ];

    protected $casts = [
        'cobrado_en' => 'datetime',
        'auditado_at' => 'datetime',
        'pago_decidido_at' => 'datetime',
        'resultado_controles' => 'array',
        'monto_cobrado' => 'decimal:2',
    ];

    // public function voucher()
    // {
    //     return $this->belongsTo(Voucher::class);
    // }
    public function voucher()
    {
    return $this->belongsTo(\App\Models\Voucher::class, 'voucher_id');
    }
    public function rendicion()
    {
        return $this->belongsTo(\App\Models\VoucherRendicion::class, 'voucher_rendicion_id');
    }

    public function auditor()
    {
        return $this->belongsTo(\App\Models\User::class, 'auditor_id');
    }

    public function decisorPago()
    {
        return $this->belongsTo(\App\Models\User::class, 'pago_decidido_por');
    }

}
