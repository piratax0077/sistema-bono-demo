<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherCobroProgramacion extends Model
{
    protected $table = 'voucher_cobro_programaciones';

    protected $fillable = [
        'user_id', 'frecuencia', 'activo', 'proxima_ejecucion_at',
        'ultima_ejecucion_at', 'ultimo_total_procesado', 'ultimo_resultado',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'proxima_ejecucion_at' => 'datetime',
        'ultima_ejecucion_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function siguienteEjecucion(string $frecuencia, $desde = null)
    {
        $fecha = ($desde ? \Illuminate\Support\Carbon::parse($desde) : now())->copy();

        return match ($frecuencia) {
            'diario' => $fecha->addDay(),
            'quincenal' => $fecha->addWeeks(2),
            'mensual' => $fecha->addMonthNoOverflow(),
            default => $fecha->addWeek(),
        };
    }
}
