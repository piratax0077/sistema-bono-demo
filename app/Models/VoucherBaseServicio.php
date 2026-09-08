<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherBaseServicio extends Model
{
    protected $table = 'voucher_base_servicios';

    protected $fillable = [
        'external_id', 'codigo', 'nombre', 'tipo_servicio', 'especialidad',
        'nivel_bono', 'valor_referencial', 'otros', 'estado',
        'vigente_desde', 'vigente_hasta',
    ];

    protected $casts = [
        'otros' => 'array',
        'vigente_desde' => 'date',
        'vigente_hasta' => 'date',
    ];

    public function relaciones()
    {
        return $this->hasMany(VoucherBaseRelacion::class, 'servicio_id');
    }
}
