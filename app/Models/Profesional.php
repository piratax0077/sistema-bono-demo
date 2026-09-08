<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Profesional extends Model
{
    protected $table = 'profesionales';

    protected $guarded = [];

    public function scopeActivos($query)
    {
        if (Schema::hasColumn($this->getTable(), 'activo')) {
            $query->where('activo', 1);
        }

        if (Schema::hasColumn($this->getTable(), 'estado')) {
            $query->whereIn('estado', ['activo', 'vigente', 1, true]);
        }

        return $query;
    }

    public function vouchers()
    {
        return $this->hasMany(Voucher::class, 'profesional_id');
    }

    public function getNombreMostrableAttribute(): string
    {
        $nombreCompleto = trim(implode(' ', array_filter([
            $this->getAttribute('nombre'),
            $this->getAttribute('apellido_uno'),
            $this->getAttribute('apellido_dos'),
        ])));

        if ($nombreCompleto !== '') {
            return $nombreCompleto;
        }

        return (string) ($this->getAttribute('nombre')
            ?: $this->getAttribute('name')
            ?: $this->getAttribute('nombre_completo')
            ?: $this->getAttribute('razon_social')
            ?: 'Profesional sin nombre');
    }

    public function getRutMostrableAttribute(): ?string
    {
        return $this->getAttribute('rut')
            ?: $this->getAttribute('run')
            ?: $this->getAttribute('documento');
    }

    public function getEspecialidadMostrableAttribute(): ?string
    {
        $especialidad = $this->getAttribute('especialidad')
            ?: $this->getAttribute('especialidad_nombre')
            ?: $this->getAttribute('profesion');

        if ($especialidad) {
            return $especialidad;
        }

        $idEspecialidad = $this->getAttribute('id_especialidad');

        return $idEspecialidad ? 'Especialidad #'.$idEspecialidad : null;
    }

    public function getEmailMostrableAttribute(): ?string
    {
        return $this->getAttribute('email')
            ?: $this->getAttribute('correo')
            ?: $this->getAttribute('mail');
    }

    public function getTelefonoMostrableAttribute(): ?string
    {
        return $this->getAttribute('telefono')
            ?: $this->getAttribute('telefono_uno')
            ?: $this->getAttribute('telefono_dos')
            ?: $this->getAttribute('fono')
            ?: $this->getAttribute('celular');
    }

    public function getDireccionMostrableAttribute(): ?string
    {
        return $this->getAttribute('direccion')
            ?: $this->getAttribute('direccion_atencion')
            ?: $this->getAttribute('lugar_atencion');
    }
}
