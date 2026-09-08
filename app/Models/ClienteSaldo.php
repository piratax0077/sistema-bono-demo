<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClienteSaldo extends Model
{
    protected $fillable = [
        'voucher_id',
        'cliente_nombre',
        'cliente_rut_hash',
        'monto',
        'origen',
        'estado',
        'descripcion',
        'voucher_consumido_id',
        'consumido_en',
    ];
    public function voucher()
{
    return $this->belongsTo(Voucher::class, 'voucher_id');
}
public function voucherConsumido()
{
    return $this->belongsTo(Voucher::class, 'voucher_consumido_id');
}
}
