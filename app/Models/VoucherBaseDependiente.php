<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherBaseDependiente extends Model
{
    protected $table = 'voucher_base_dependientes';

    protected $fillable = [
        'usuario_id', 'external_id', 'nombre', 'rut_hash', 'rut_sha256', 'rut_encrypted',
        'direccion_encrypted', 'fecha_nacimiento_encrypted', 'parentesco',
        'otros', 'estado', 'vigente_desde', 'vigente_hasta',
    ];

    protected $casts = [
        'otros' => 'array',
        'vigente_desde' => 'date',
        'vigente_hasta' => 'date',
    ];

    public function usuario()
    {
        return $this->belongsTo(VoucherBaseUsuario::class, 'usuario_id');
    }

    public function relaciones()
    {
        return $this->hasMany(VoucherBaseRelacion::class, 'dependiente_id');
    }
}
