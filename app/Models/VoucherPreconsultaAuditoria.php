<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherPreconsultaAuditoria extends Model
{
    protected $table = 'voucher_preconsulta_auditorias';

    protected $fillable = [
        'preconsulta_id',
        'estado',
        'motivo',
        'contradicciones',
        'intentos_fallidos_count',
        'intentos_fallidos',
        'porques',
        'contexto',
        'auditor_id',
        'resolucion',
        'resuelto_at',
    ];

    protected $casts = [
        'contradicciones' => 'array',
        'intentos_fallidos' => 'array',
        'porques' => 'array',
        'contexto' => 'array',
        'resuelto_at' => 'datetime',
    ];

    public function preconsulta()
    {
        return $this->belongsTo(VoucherPreconsulta::class, 'preconsulta_id');
    }

    public function auditor()
    {
        return $this->belongsTo(User::class, 'auditor_id');
    }
}
