<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgendaOnlineHorario extends Model
{
    protected $table = 'agenda_online_horarios';

    protected $fillable = [
        'profesional_id', 'servicio_id', 'fecha_hora', 'duracion_minutos',
        'centro_nombre', 'centro_email', 'centro_telefono', 'centro_direccion',
        'lugar_atencion', 'estado', 'reservado_por', 'voucher_id',
    ];

    protected $casts = ['fecha_hora' => 'datetime'];

    public function profesional() { return $this->belongsTo(VoucherProfesional::class, 'profesional_id'); }
    public function servicio() { return $this->belongsTo(VoucherServicio::class, 'servicio_id'); }
    public function voucher() { return $this->belongsTo(Voucher::class, 'voucher_id'); }
}
