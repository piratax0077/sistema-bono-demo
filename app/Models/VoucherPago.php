<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoucherPago extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_id',
        'monto_pagado_usuario',
        'metodo_pago',
        'estado_pago',
        'comprobante',
    ];

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }
}
