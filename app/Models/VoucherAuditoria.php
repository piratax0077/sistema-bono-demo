<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoucherAuditoria extends Model
{
    use HasFactory;

    protected $table = 'voucher_auditorias';

    protected $fillable = [

        'voucher_id',
        'accion',
        'usuario_tipo',
        'usuario_id',
        'descripcion',
        'ip',
    ];

    public function voucher()
    {
        return $this->belongsTo(
            \App\Models\Voucher::class,
            'voucher_id'
        );
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
