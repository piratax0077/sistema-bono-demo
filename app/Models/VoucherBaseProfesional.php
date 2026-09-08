<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherBaseProfesional extends Model
{
    protected $table = 'voucher_base_profesionales';

    protected $fillable = [
        'external_id', 'nombre', 'rut_hash', 'rut_sha256', 'rut_encrypted',
        'direccion_encrypted', 'fecha_nacimiento_encrypted',
        'profesion', 'especialidad', 'nivel_bono', 'email', 'telefono',
        'otros', 'estado', 'vigente_desde', 'vigente_hasta',
    ];

    protected $casts = [
        'otros' => 'array',
        'vigente_desde' => 'date',
        'vigente_hasta' => 'date',
    ];

    public function relaciones()
    {
        return $this->hasMany(VoucherBaseRelacion::class, 'profesional_id');
    }
}
