<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoucherProfesional extends Model
{
    use HasFactory;

    protected $table = 'voucher_profesionales';

    protected $fillable = [

        'nombre',
        'rut',
        'especialidad',
        'telefono',
        'email',
        'activo',

        'banco',
        'tipo_cuenta',
        'numero_cuenta',
        'titular_cuenta',
        'rut_cuenta',
    ];
}
