<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TotemVentaDetalle extends Model
{
    protected $table = 'totem_venta_detalles';

    protected $fillable = [
        'venta_id',
        'tipo',
        'referencia_id',
        'cantidad',
        'precio',
    ];

    public function venta()
    {
        return $this->belongsTo(TotemVenta::class, 'venta_id');
    }
}
