<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoucherRendicion extends Model
{
    use HasFactory;

    protected $table = 'voucher_rendiciones';

    protected $fillable = [
        'veterinario_nombre',
        'sucursal',
        'total_cobrado',
        'cantidad_vouchers',
        'estado',
        'rendida_en',
        'pagada_en',
    ];

    public function cobros()
    {
        return $this->hasMany(\App\Models\VoucherCobro::class, 'voucher_rendicion_id');
    }
    public function liquidaciones()
{
    return $this->hasMany(
        \App\Models\VoucherLiquidacion::class,
        'voucher_rendicion_id'
    );
}



}
