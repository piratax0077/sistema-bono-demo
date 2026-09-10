<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo',
        'qr_token',
        'qr_firma',
        'qr_expira',
        'qr_usado',
        'qr_usado_at',
        'cliente_rut_hash',
        'vendedor_id',
        'cliente_id',
        'mascota_id',
        'criadero_cachorro_id',
        'cliente_rut',
        'cliente_nombre',
        'cliente_telefono',
        'cliente_email',
        'beneficiario_tipo',
        'beneficiario_base_usuario_id',
        'beneficiario_dependiente_id',
        'beneficiario_nombre',
        'beneficiario_rut',
        'beneficiario_rut_hash',
        'beneficiario_parentesco',
        'beneficiario_direccion',
        'beneficiario_fecha_nacimiento',
        'beneficiario_edad',
        'tipo_servicio',
        'valor',
        'porcentaje_descuento',
        'estado',
        'fecha_vencimiento',
        'usado_en',
        'copago_usuario',
        'saldo_veterinario',
        'comision_veterchile',
        'profesional_id',
        'servicio_id',
        'cliente_aceptado_en',
        'cliente_rechazado_en',
        'motivo_rechazo_cliente',
        'mascota_id',
        'otp_hash',
        'otp_expira',
        'otp_validado_at',
        'invalidado_en',
        'motivo_invalidacion',
        'copago_devuelto',
        'saldo_cliente_aplicado',
        'totem_venta_id',
        'mascota_nombre',
        'mascota_edad',
        'mascota_raza',

        'prestador_rut',
        'prestador_nombre',
        'prestador_especialidad',
        'prestador_email',
        'prestador_telefono',
        'prestador_direccion',

        'valor_total',
        'agenda_id',
        'atencion_id',
        'profesional_atendio_id',
        'asistente_valido_id',
        'atencion_cerrada_at',
        'validado_at',
        'ip_profesional',
        'ip_asistente',
        'estado_validacion',
        'riesgo_validacion',



    ];
    public function cobros()
    {
        return $this->hasMany(VoucherCobro::class);
    }
    public function pagos()
{
    return $this->hasMany(\App\Models\VoucherPago::class);
}
public function vendedor()
{
    return $this->belongsTo(
        \App\Models\VoucherVendedor::class,
        'vendedor_id'
    );
}
public function profesional()
{
    return $this->belongsTo(
        \App\Models\VoucherProfesional::class,
        'profesional_id'
    );
}
public function profesionalCatalogo()
{
    return $this->belongsTo(
        \App\Models\Profesional::class,
        'profesional_id'
    );
}
public function servicio()
{
    return $this->belongsTo(\App\Models\VoucherServicio::class, 'servicio_id');
}
public function beneficiarioBaseUsuario()
{
    return $this->belongsTo(\App\Models\VoucherBaseUsuario::class, 'beneficiario_base_usuario_id');
}
public function beneficiarioDependiente()
{
    return $this->belongsTo(\App\Models\VoucherBaseDependiente::class, 'beneficiario_dependiente_id');
}
public function cliente()
{
    return $this->belongsTo(\App\Models\User::class, 'cliente_id');
}
public function mascota()
{
    return $this->belongsTo(
        \App\Models\VoucherMascota::class,
        'mascota_id'
    );
}
public function getClienteRutVisibleAttribute()
{
    try {
        return Crypt::decryptString($this->cliente_rut);
    } catch (\Exception $e) {
        return $this->cliente_rut;
    }
}
public function getBeneficiarioRutVisibleAttribute()
{
    return $this->decryptVisible($this->beneficiario_rut) ?: $this->cliente_rut_visible;
}
public function getBeneficiarioDireccionVisibleAttribute()
{
    return $this->decryptVisible($this->beneficiario_direccion)
        ?: $this->decryptVisible(optional($this->beneficiarioDependiente)->direccion_encrypted)
        ?: $this->decryptVisible(optional($this->beneficiarioBaseUsuario)->direccion_encrypted);
}
public function getBeneficiarioFechaNacimientoVisibleAttribute()
{
    return $this->decryptVisible($this->beneficiario_fecha_nacimiento)
        ?: $this->decryptVisible(optional($this->beneficiarioDependiente)->fecha_nacimiento_encrypted)
        ?: $this->decryptVisible(optional($this->beneficiarioBaseUsuario)->fecha_nacimiento_encrypted);
}
public function getBeneficiarioEdadVisibleAttribute()
{
    if ($this->beneficiario_edad !== null) {
        return (int) $this->beneficiario_edad;
    }

    if (! filled($this->beneficiario_fecha_nacimiento_visible)) {
        return null;
    }

    try {
        return Carbon::parse($this->beneficiario_fecha_nacimiento_visible)->age;
    } catch (\Throwable $exception) {
        return null;
    }
}
private function decryptVisible($value): ?string
{
    if (! filled($value)) {
        return null;
    }

    try {
        return Crypt::decryptString((string) $value);
    } catch (\Throwable $exception) {
        return (string) $value;
    }
}
public function agenda()
{
    return $this->hasOne(\App\Models\VoucherAgenda::class, 'voucher_id');
}

/**
 * Etapa (1 a 5) del recorrido de atención según el estado local del voucher
 * y el último id_estado Med-SDI sincronizado. Usado para mostrar el stepper
 * "Tu recorrido de atención" con el estado real en cualquier vista.
 */
public function demoRecorridoStep(): int
{
    $idEstadoRemoto = (int) optional($this->agenda)->medichile_estado_id;

    return match (true) {
        $this->estado === 'pendiente_confirmacion' => 1,
        $this->estado === 'pendiente_pago' => 2,
        $idEstadoRemoto === 4 => 4,
        in_array($idEstadoRemoto, [5, 6], true) => 5,
        default => 3,
    };
}

public function atencion()
{
    return $this->hasOne(\App\Models\VoucherAtencion::class, 'voucher_id');
}
}
