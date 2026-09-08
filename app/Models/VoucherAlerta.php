<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VoucherAlerta extends Model
{
    use HasFactory;

    protected $table = 'voucher_alertas';

    protected $fillable = [
        'voucher_id',
        'tipo_alerta',
        'nivel',
        'descripcion',
        'resuelta',
    ];


    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
    }
}
