<?php

namespace Database\Seeders;

use App\Models\Voucher;
use App\Models\VoucherAgenda;
use App\Models\VoucherAuditoria;
use App\Services\MedichileAgendaService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class MedichileAgendaDemoSeeder extends Seeder
{
    public function run(): void
    {
        $voucher = Voucher::where('codigo', 'DEMO-PACIENTE-PRUEBA')->firstOrFail();
        $agenda = VoucherAgenda::where('voucher_id', $voucher->id)->firstOrFail();
        $medichile = DB::connection('medichile');

        $paciente = $medichile->table('pacientes')
            ->whereRaw("REPLACE(REPLACE(REPLACE(rut, '.', ''), '-', ''), ' ', '') = ?", ['102115686'])
            ->first();

        if (! $paciente) {
            throw new RuntimeException('No se encontró el paciente demo 10211568-6 en Medichile.');
        }

        $profesional = $medichile->table('profesionales')
            ->whereRaw("REPLACE(REPLACE(REPLACE(rut, '.', ''), '-', ''), ' ', '') = ?", ['111111111'])
            ->first();

        if (! $profesional) {
            $profesionalId = $medichile->table('profesionales')->insertGetId([
                'nombre' => 'Profesional',
                'apellido_uno' => 'Revisión',
                'apellido_dos' => 'Demo',
                'sexo' => 'M',
                'rut' => '11111111-1',
                'email' => 'profesional@gmail.com',
                'telefono_uno' => '+56911111111',
                'estado' => 1,
                'certificado' => 1,
                'id_tipo_atencion' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $profesional = $medichile->table('profesionales')->where('id', $profesionalId)->first();
        }

        $descripcion = 'SDI-'.$voucher->codigo;
        $hora = $medichile->table('horas_medicas')->where('descripcion', $descripcion)->first();
        $inicio = now()->startOfMinute();

        if (! $hora) {
            $horaId = $medichile->table('horas_medicas')->insertGetId([
                'fecha_consulta' => now()->toDateString(),
                'hora_inicio' => $inicio->format('H:i:s'),
                'hora_termino' => $inicio->copy()->addMinutes(30)->format('H:i:s'),
                'descripcion' => $descripcion,
                'observaciones' => 'Hora demo vinculada al bono '.$voucher->codigo,
                'id_profesional' => $profesional->id,
                'id_paciente' => $paciente->id,
                'id_estado' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $horaId = $hora->id;
            $medichile->table('horas_medicas')->where('id', $horaId)->update([
                'fecha_consulta' => now()->toDateString(),
                'id_profesional' => $profesional->id,
                'id_paciente' => $paciente->id,
                'updated_at' => now(),
            ]);
        }

        $agenda->update([
            'medichile_hora_medica_id' => $horaId,
            'medichile_sync_error' => null,
        ]);

        $sync = app(MedichileAgendaService::class)->dejarPacienteEnEspera($voucher->fresh(), $agenda->fresh());

        $agenda->update([
            'medichile_hora_medica_id' => $sync['hora_medica_id'],
            'medichile_estado_id' => $sync['estado_id'],
            'medichile_sincronizado_at' => $sync['sincronizado_at'],
            'medichile_sync_error' => null,
        ]);

        VoucherAuditoria::updateOrCreate(
            [
                'voucher_id' => $voucher->id,
                'accion' => 'demo_agenda_medichile_sincronizada',
            ],
            [
                'usuario_tipo' => 'sistema',
                'usuario_id' => null,
                'descripcion' => 'Demo vinculada con la hora Medichile #'.$sync['hora_medica_id'].' y actualizada al estado real Espera (ID 4).',
                'ip' => '127.0.0.1',
            ]
        );
    }
}
