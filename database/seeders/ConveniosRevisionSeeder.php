<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\VoucherBaseProfesional;
use App\Models\VoucherBaseRelacion;
use App\Models\VoucherBaseServicio;
use App\Models\VoucherBaseUsuario;
use App\Models\VoucherProfesional;
use App\Models\VoucherServicio;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Crypt;

class ConveniosRevisionSeeder extends Seeder
{
    public function run(): void
    {
        $paciente = User::where('email', 'paciente@gmail.com')->firstOrFail();
        $profesionalUser = User::where('email', 'profesional@gmail.com')->firstOrFail();
        $profesional = VoucherProfesional::findOrFail($profesionalUser->profesional_id);

        $usuarioBase = VoucherBaseUsuario::updateOrCreate(
            ['rut_hash' => $this->rutHmac($paciente->rut)],
            [
                'external_id' => 'DEMO-PACIENTE-REVISION',
                'nombre' => $paciente->name,
                'rut_sha256' => $this->rutSha($paciente->rut),
                'rut_encrypted' => Crypt::encryptString($this->normalizarRut($paciente->rut)),
                'direccion_encrypted' => Crypt::encryptString('Av. Providencia 1234, Santiago'),
                'fecha_nacimiento_encrypted' => Crypt::encryptString('1990-05-15'),
                'otros' => ['grupo_ingreso' => 'A'],
                'estado' => 'activo',
                'vigente_desde' => now()->subYear()->toDateString(),
                'vigente_hasta' => now()->addYear()->toDateString(),
            ]
        );

        $profesionalBase = VoucherBaseProfesional::updateOrCreate(
            ['external_id' => 'DEMO-PROFESIONAL-REVISION'],
            [
                'nombre' => $profesional->nombre,
                'rut_hash' => $this->rutHmac($profesional->rut),
                'rut_sha256' => $this->rutSha($profesional->rut),
                'rut_encrypted' => Crypt::encryptString($this->normalizarRut($profesional->rut)),
                'profesion' => 'Médico cirujano',
                'especialidad' => 'Medicina general',
                'nivel_bono' => 'nivel_1',
                'email' => $profesional->email,
                'telefono' => $profesional->telefono,
                'otros' => ['voucher_profesional_id' => $profesional->id],
                'estado' => 'activo',
                'vigente_desde' => now()->subYear()->toDateString(),
                'vigente_hasta' => now()->addYear()->toDateString(),
            ]
        );

        foreach ([
            ['nivel' => 'nivel_1', 'especialidad' => 'Medicina general', 'servicio' => 'Consulta medicina general', 'valor' => 25000, 'copago' => 5000],
            ['nivel' => 'nivel_2', 'especialidad' => 'Cardiología', 'servicio' => 'Consulta cardiología', 'valor' => 42000, 'copago' => 9000],
            ['nivel' => 'nivel_3', 'especialidad' => 'Dermatología', 'servicio' => 'Consulta dermatología', 'valor' => 55000, 'copago' => 14000],
        ] as $item) {
            $servicio = VoucherServicio::updateOrCreate(['nombre' => $item['servicio']], [
                'descripcion' => 'Prestación de convenio '.$item['nivel'].' para revisión.',
                'valor_base' => $item['valor'],
                'copago_base' => $item['copago'],
                'comision_veterchile' => 1000,
                'activo' => true,
            ]);
            $servicioBase = VoucherBaseServicio::updateOrCreate(
                ['external_id' => 'DEMO-'.strtoupper($item['nivel'])],
                [
                    'codigo' => 'CONV-'.strtoupper($item['nivel']),
                    'nombre' => $item['servicio'],
                    'tipo_servicio' => 'consulta',
                    'especialidad' => $item['especialidad'],
                    'nivel_bono' => $item['nivel'],
                    'valor_referencial' => $item['valor'],
                    'otros' => ['voucher_servicio_id' => $servicio->id],
                    'estado' => 'activo',
                    'vigente_desde' => now()->subYear()->toDateString(),
                    'vigente_hasta' => now()->addYear()->toDateString(),
                ]
            );
            VoucherBaseRelacion::updateOrCreate(
                ['external_id' => 'DEMO-CONVENIO-'.strtoupper($item['nivel'])],
                [
                    'usuario_id' => $usuarioBase->id,
                    'profesional_id' => $profesionalBase->id,
                    'servicio_id' => $servicioBase->id,
                    'tipo_prestador' => 'profesional',
                    'tipo_relacion' => 'convenio_isapre',
                    'nivel_bono' => $item['nivel'],
                    'estado' => 'vigente',
                    'vigente_desde' => now()->subYear()->toDateString(),
                    'vigente_hasta' => now()->addYear()->toDateString(),
                    'requiere_auditoria' => false,
                    'otros' => ['valor_convenio' => $item['valor'], 'copago_convenio' => $item['copago']],
                ]
            );
        }
    }

    private function normalizarRut(?string $rut): string { return strtoupper(preg_replace('/[^0-9K]/i', '', (string) $rut)); }
    private function rutHmac(?string $rut): string { return hash_hmac('sha256', $this->normalizarRut($rut), (string) config('app.key')); }
    private function rutSha(?string $rut): string { return hash('sha256', $this->normalizarRut($rut)); }
}
