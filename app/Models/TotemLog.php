<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TotemLog extends Model
{
    protected $table = 'totem_logs';

    protected $fillable = [
        'totem_id',
        'evento',
        'detalle',
        'ip',
    ];

    public function totem()
    {
        return $this->belongsTo(Totem::class);
    }
}
