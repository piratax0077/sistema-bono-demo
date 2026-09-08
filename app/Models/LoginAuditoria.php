<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginAuditoria extends Model
{
    protected $fillable = [
        'user_id',
        'email',
        'resultado',
        'ip',
        'user_agent',
    ];
}
