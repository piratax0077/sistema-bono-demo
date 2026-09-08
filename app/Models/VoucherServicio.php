<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoucherServicio extends Model
{
    use HasFactory;

    protected $table = 'voucher_servicios';

    protected $fillable = [
        'nombre',
        'descripcion',
        'valor_base',
        'copago_base',
        'comision_veterchile',
        'activo',
    ];
}
