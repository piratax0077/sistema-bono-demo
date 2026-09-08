<?php

namespace Database\Seeders;

use App\Models\AgendaOnlineHorario;
use App\Models\User;
use App\Models\VoucherProfesional;
use App\Models\VoucherServicio;
use Illuminate\Database\Seeder;

class AgendaOnlineDemoSeeder extends Seeder
{
    public function run(): void
    {
        $profesionalUser = User::where('email', 'profesional@gmail.com')->firstOrFail();
        $profesional = VoucherProfesional::findOrFail($profesionalUser->profesional_id);
        $servicio = VoucherServicio::where('nombre', 'Consulta medicina general')->first()
            ?: VoucherServicio::where('activo', true)->orderBy('id')->firstOrFail();

        for ($dia = 1; $dia <= 30; $dia++) {
            foreach ([[9, 0], [11, 0], [15, 30]] as [$hora, $minuto]) {
                $fecha = now()->addDays($dia)->setTime($hora, $minuto, 0);
                AgendaOnlineHorario::firstOrCreate(
                    ['profesional_id' => $profesional->id, 'fecha_hora' => $fecha],
                    [
                        'servicio_id' => $servicio->id,
                        'duracion_minutos' => 30,
                        'centro_nombre' => 'Centro Médico de Prueba',
                        'centro_email' => 'recepcion@centromedico.cl',
                        'centro_telefono' => '+56912345678',
                        'centro_direccion' => 'Av. Concón 123, Concón',
                        'lugar_atencion' => 'Consulta 204 · Box 3',
                        'estado' => 'disponible',
                        'reservado_por' => null,
                        'voucher_id' => null,
                    ]
                );
            }
        }
    }
}
