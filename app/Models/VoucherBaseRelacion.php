<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherBaseRelacion extends Model
{
    protected $table = 'voucher_base_relaciones';

    protected $fillable = [
        'external_id', 'usuario_id', 'dependiente_id', 'profesional_id',
        'laboratorio_id', 'servicio_id', 'tipo_prestador', 'tipo_relacion',
        'nivel_bono', 'estado', 'vigente_desde', 'vigente_hasta',
        'requiere_auditoria', 'motivo_auditoria', 'restricciones', 'otros',
    ];

    protected $casts = [
        'requiere_auditoria' => 'boolean',
        'restricciones' => 'array',
        'otros' => 'array',
        'vigente_desde' => 'date',
        'vigente_hasta' => 'date',
    ];

    public function usuario()
    {
        return $this->belongsTo(VoucherBaseUsuario::class, 'usuario_id');
    }

    public function dependiente()
    {
        return $this->belongsTo(VoucherBaseDependiente::class, 'dependiente_id');
    }

    public function profesional()
    {
        return $this->belongsTo(VoucherBaseProfesional::class, 'profesional_id');
    }

    public function laboratorio()
    {
        return $this->belongsTo(VoucherBaseLaboratorio::class, 'laboratorio_id');
    }

    public function servicio()
    {
        return $this->belongsTo(VoucherBaseServicio::class, 'servicio_id');
    }
}
