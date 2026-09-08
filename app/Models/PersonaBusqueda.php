<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonaBusqueda extends Model
{
    protected $connection = 'personas_fast';

    // Tabla canónica de la base propia de personas del demo.
    protected $table = 'personas';

    protected $fillable = [
        'id',
        'rut_original',
        'rut_normalizado',
        'rut_cuerpo',
        'rut_dv',
        'nombre1',
        'appaterno',
        'apmaterno',
        'nombre_completo',
        'estado',
    ];

    public static function normalizarRut(?string $rut): string
    {
        return strtoupper(preg_replace('/[^0-9K]/i', '', (string) $rut));
    }

    public function scopePorRut($query, ?string $rut)
    {
        return $query->where('rut_normalizado', self::normalizarRut($rut));
    }

    public function scopePorNombre($query, ?string $texto)
    {
        $terminos = collect(preg_split('/\s+/', trim((string) $texto)))
            ->filter()
            ->map(fn ($term) => '+'.$term)
            ->implode(' ');

        if ($terminos === '') {
            return $query;
        }

        return $query->whereRaw(
            'MATCH(nombre_completo) AGAINST(? IN BOOLEAN MODE)',
            [$terminos]
        );
    }
}
