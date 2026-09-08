<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherBaseLaboratorio extends Model
{
    protected $table = 'voucher_base_laboratorios';

    protected $fillable = [
        'external_id', 'nombre', 'rut_hash', 'rut_sha256', 'rut_encrypted',
        'direccion_encrypted', 'tipo_laboratorio', 'especialidad',
        'nivel_bono', 'email', 'telefono', 'otros', 'estado',
        'vigente_desde', 'vigente_hasta',
    ];

    protected $casts = [
        'otros' => 'array',
        'vigente_desde' => 'date',
        'vigente_hasta' => 'date',
    ];

    public function relaciones()
    {
        return $this->hasMany(VoucherBaseRelacion::class, 'laboratorio_id');
    }
}
