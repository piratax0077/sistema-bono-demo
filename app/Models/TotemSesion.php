<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TotemSesion extends Model
{
    protected $table = 'totem_sesiones';

    protected $fillable = [
        'totem_id',
        'token',
        'token_hash',
        'inicio',
        'fin',
        'expira_at',
        'ip',
    ];

    protected $hidden = ['token', 'token_hash'];

    protected $casts = [
        'inicio' => 'datetime',
        'fin' => 'datetime',
        'expira_at' => 'datetime',
    ];

    public function totem()
    {
        return $this->belongsTo(Totem::class);
    }
}
