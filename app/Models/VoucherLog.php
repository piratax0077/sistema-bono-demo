<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;





class VoucherLog extends Model
{
    protected $fillable = [
        'voucher_id',
        'user_id',
        'accion',
        'ip',
        'detalle'
    ];
}
