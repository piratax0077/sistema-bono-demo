<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IpAutorizada extends Model
{
    protected $table = 'ip_autorizadas';

    protected $fillable = [
        'rol',
        'user_id',
        'ip',
        'descripcion',
        'activo',
    ];
}
