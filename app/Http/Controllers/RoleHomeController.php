<?php

namespace App\Http\Controllers;

class RoleHomeController extends Controller
{
    public function asistente()
    {
        return view('home.perfil', $this->datos(
            'Asistente',
            'Recepción y coordinación de pacientes',
            [
                ['titulo' => 'Escritorio asistente', 'detalle' => 'Recepción, sala de espera y validaciones.', 'url' => route('asistente.escritorio')],
                ['titulo' => 'Buscar paciente', 'detalle' => 'Consultar o registrar rápidamente un paciente.', 'url' => route('personas-rapidas.prueba')],
            ]
        ));
    }

    public function profesional()
    {
        return view('home.perfil', $this->datos(
            'Profesional',
            'Agenda clínica y atención de pacientes',
            [
                ['titulo' => 'Escritorio profesional', 'detalle' => 'Pacientes en espera y atenciones actuales.', 'url' => route('profesional.escritorio')],
                ['titulo' => 'Gestión de cobros', 'detalle' => 'Atenciones habilitadas y códigos de cobro.', 'url' => route('profesional.cobros')],
            ]
        ));
    }

    public function vendedor()
    {
        return view('home.perfil', $this->datos(
            'Vendedor',
            'Venta y administración de bonos',
            [
                ['titulo' => 'Escritorio vendedor', 'detalle' => 'Resumen de la operación diaria.', 'url' => url('/escritorio-vendedor')],
                ['titulo' => 'Caja', 'detalle' => 'Administrar movimientos de caja.', 'url' => url('/vendedores/caja')],
                ['titulo' => 'Nuevo bono', 'detalle' => 'Iniciar una venta de bono.', 'url' => route('vouchers.create')],
            ]
        ));
    }

    public function administracion()
    {
        return view('home.perfil', $this->datos(
            'Administración',
            'Configuración y supervisión del sistema',
            [
                ['titulo' => 'Escritorio administrativo', 'detalle' => 'Resumen general de la plataforma.', 'url' => url('/escritorio-admin')],
                ['titulo' => 'Administrar tótems', 'detalle' => 'Equipos, estados y alertas operacionales.', 'url' => route('admin.totems.dashboard')],
                ['titulo' => 'Auditoría', 'detalle' => 'Revisar movimientos y alertas.', 'url' => url('/auditoria')],
            ]
        ));
    }

    public function contraloria()
    {
        return view('home.perfil', $this->datos(
            'Contraloría',
            'Auditoría, control y trazabilidad',
            [
                ['titulo' => 'Panel de auditoría', 'detalle' => 'Indicadores, alertas y revisiones.', 'url' => url('/auditoria')],
                ['titulo' => 'Notificaciones', 'detalle' => 'Eventos pendientes de revisión.', 'url' => route('auditoria.notificaciones')],
                ['titulo' => 'Accesos', 'detalle' => 'Historial de inicios de sesión.', 'url' => route('auditoria.logins')],
            ]
        ));
    }

    private function datos(string $perfil, string $subtitulo, array $accesos): array
    {
        return compact('perfil', 'subtitulo', 'accesos');
    }
}
