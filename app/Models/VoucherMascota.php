<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoucherMascota extends Model
{
    use HasFactory;

    protected $table = 'voucher_mascotas';

    protected $fillable = [
        'cliente_id',
        'nombre',
        'especie',
        'raza',
        'sexo',
        'fecha_nacimiento',
        'edad',
        'color',
        'microchip',
        'dueno_rut',
        'dueno_nombre',
        'dueno_telefono',
        'dueno_email',
        'dueno_rut_hash',
        'dueno_telefono_hash',
        'dueno_email_hash',
        'activo',
    ];

    protected $casts = [
        'dueno_rut' => 'encrypted',
        'dueno_nombre' => 'encrypted',
        'dueno_telefono' => 'encrypted',
        'dueno_email' => 'encrypted',
    ];

    public function vouchers()
    {
        return $this->hasMany(Voucher::class, 'mascota_id');
    }

    protected static function booted()
    {
        static::saving(function ($mascota) {
            if ($mascota->dueno_rut) {
                $mascota->dueno_rut_hash = hash(
                    'sha256',
                    self::normalizar($mascota->dueno_rut)
                );
            }

            if ($mascota->dueno_telefono) {
                $mascota->dueno_telefono_hash = hash(
                    'sha256',
                    self::normalizar($mascota->dueno_telefono)
                );
            }

            if ($mascota->dueno_email) {
                $mascota->dueno_email_hash = hash(
                    'sha256',
                    self::normalizar($mascota->dueno_email)
                );
            }
        });
    }

    public static function normalizar($valor)
    {
        return strtolower(trim(str_replace(['.', '-', ' '], '', $valor)));
    }
}
