<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherDeliveryRequest extends Model
{
    protected $fillable = [
        'voucher_id',
        'cliente_user_id',
        'canal',
        'destino_tipo',
        'destino',
        'estado',
        'mensaje',
        'action_url',
        'enviado_en',
        'metadata',
    ];

    protected $casts = [
        'enviado_en' => 'datetime',
        'metadata' => 'array',
    ];

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    public function cliente()
    {
        return $this->belongsTo(User::class, 'cliente_user_id');
    }
}
