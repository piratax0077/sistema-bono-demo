<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherBaseUsuario extends Model
{
    protected $table = 'voucher_base_usuarios';

    protected $fillable = [
        'external_id', 'nombre', 'rut_hash', 'rut_sha256', 'rut_encrypted',
        'direccion_encrypted', 'fecha_nacimiento_encrypted', 'otros',
        'estado', 'vigente_desde', 'vigente_hasta',
    ];

    protected $casts = [
        'otros' => 'array',
        'vigente_desde' => 'date',
        'vigente_hasta' => 'date',
    ];

    public function dependientes()
    {
        return $this->hasMany(VoucherBaseDependiente::class, 'usuario_id');
    }

    public function relaciones()
    {
        return $this->hasMany(VoucherBaseRelacion::class, 'usuario_id');
    }
}
