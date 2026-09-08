<?php

namespace Database\Seeders;

use App\Models\IpAutorizada;
use App\Models\Cliente;
use App\Models\ClienteDispositivo;
use App\Models\Totem;
use App\Models\User;
use App\Models\VoucherBaseProfesional;
use App\Models\VoucherBaseRelacion;
use App\Models\VoucherBaseServicio;
use App\Models\VoucherBaseUsuario;
use App\Models\VoucherProfesional;
use App\Models\VoucherServicio;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $password = Hash::make('Sdi2026*');

        foreach ([
            ['name' => 'Administrador SDI', 'email' => 'admin@sdi.local', 'rol' => 'admin'],
            ['name' => 'Auditor SDI', 'email' => 'auditor@sdi.local', 'rol' => 'auditor'],
            ['name' => 'Asistente SDI', 'email' => 'asistente@sdi.local', 'rol' => 'asistente'],
            ['name' => 'Vendedor SDI', 'email' => 'vendedor@sdi.local', 'rol' => 'vendedor'],
            [
                'name' => 'Beneficiario SDI',
                'email' => 'beneficiario@sdi.local',
                'rol' => 'cliente',
                'rut' => '10211568-6',
                'telefono' => '+56995474660',
            ],
        ] as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => $password,
                    'rol' => $user['rol'],
                    'rut' => $user['rut'] ?? null,
                    'telefono' => $user['telefono'] ?? null,
                    'activo' => true,
                ]
            );
        }

        $rutBeneficiario = strtoupper(str_replace(['.', '-', ' '], '', '10211568-6'));

        $clienteDemo = Cliente::updateOrCreate(
            ['rut_hash' => hash('sha256', $rutBeneficiario)],
            [
                'nombre' => 'Beneficiario SDI',
                'rut' => Crypt::encryptString($rutBeneficiario),
                'telefono' => '+56995474660',
                'email' => 'beneficiario@sdi.local',
                'tipo' => 'dueno_mascota',
                'estado' => 'activo',
                'fecha_inscripcion' => now(),
            ]
        );

        ClienteDispositivo::updateOrCreate(
            ['imei_hash' => hash('sha256', 'APP-DEMO-10211568')],
            [
                'cliente_id' => $clienteDemo->id,
                'device_token' => 'demo-local-10211568',
                'nombre_dispositivo' => 'App autorizadora demo beneficiario',
                'estado' => 'activo',
                'ultimo_uso_at' => now(),
                'ip_registro' => '127.0.0.1',
            ]
        );

        foreach (['admin', 'auditor'] as $rol) {
            foreach (['127.0.0.1', '::1'] as $ip) {
                IpAutorizada::updateOrCreate(
                    ['rol' => $rol, 'ip' => $ip],
                    [
                        'descripcion' => 'Acceso local de desarrollo para '.$rol,
                        'activo' => true,
                    ]
                );
            }
        }

        Totem::firstOrCreate(
            ['codigo' => 'TOTEM001'],
            [
                'nombre' => 'Tótem atención local',
                'ubicacion' => 'Recepción central',
                'ip_autorizada' => '127.0.0.1',
                'version' => '1.0.0',
                'auth_secret_hash' => Hash::make('ClaveTotemLocal2026'),
                'activo' => true,
                'estado_operacional' => 'ok',
            ]
        );

        $servicioDemo = VoucherServicio::firstOrCreate(
            ['nombre' => 'Consulta general SDI'],
            [
                'descripcion' => 'Servicio demo para validar emisión de bono desde tótem.',
                'valor_base' => 25000,
                'copago_base' => 5000,
                'comision_veterchile' => 0,
                'activo' => true,
            ]
        );

        $profesionalDemo = VoucherProfesional::firstOrCreate(
            ['rut' => '6187674-k'],
            [
                'nombre' => 'Jaime Kriman Astorga',
                'especialidad' => 'Medicina general',
                'telefono' => '+56995474660',
                'email' => 'jkriman@gmail.com',
                'activo' => true,
            ]
        );

        $hashRut = function ($rut) {
            $normalizado = strtoupper(preg_replace('/[^0-9K]/i', '', (string) $rut));

            return [
                $normalizado,
                hash_hmac('sha256', $normalizado, (string) config('app.key')),
                hash('sha256', $normalizado),
            ];
        };

        [$rutUsuarioNormalizado, $rutUsuarioHmac, $rutUsuarioSha] = $hashRut('10211568-6');
        [$rutProfesionalNormalizado, $rutProfesionalHmac, $rutProfesionalSha] = $hashRut($profesionalDemo->rut);

        $baseUsuario = VoucherBaseUsuario::updateOrCreate(
            ['rut_hash' => $rutUsuarioHmac],
            [
                'external_id' => 'BENEFICIARIO-DEMO-10211568',
                'nombre' => 'Beneficiario SDI',
                'rut_sha256' => $rutUsuarioSha,
                'rut_encrypted' => Crypt::encryptString($rutUsuarioNormalizado),
                'estado' => 'activo',
                'vigente_desde' => now()->subDay()->toDateString(),
                'vigente_hasta' => now()->addYear()->toDateString(),
                'otros' => ['origen' => 'seed_demo'],
            ]
        );

        $baseProfesional = VoucherBaseProfesional::updateOrCreate(
            ['rut_hash' => $rutProfesionalHmac],
            [
                'external_id' => 'PROF-DEMO-6187674K',
                'nombre' => $profesionalDemo->nombre,
                'rut_sha256' => $rutProfesionalSha,
                'rut_encrypted' => Crypt::encryptString($rutProfesionalNormalizado),
                'profesion' => 'Médico',
                'especialidad' => $profesionalDemo->especialidad,
                'nivel_bono' => 'nivel_1',
                'email' => $profesionalDemo->email,
                'telefono' => $profesionalDemo->telefono,
                'estado' => 'activo',
                'vigente_desde' => now()->subDay()->toDateString(),
                'vigente_hasta' => now()->addYear()->toDateString(),
                'otros' => ['voucher_profesional_id' => $profesionalDemo->id],
            ]
        );

        $baseServicio = VoucherBaseServicio::updateOrCreate(
            ['codigo' => 'CONSULTA_GENERAL_SDI'],
            [
                'external_id' => 'SERVICIO-DEMO-'.$servicioDemo->id,
                'nombre' => $servicioDemo->nombre,
                'tipo_servicio' => 'consulta',
                'especialidad' => 'Medicina general',
                'nivel_bono' => 'nivel_1',
                'valor_referencial' => $servicioDemo->valor_base,
                'estado' => 'activo',
                'vigente_desde' => now()->subDay()->toDateString(),
                'vigente_hasta' => now()->addYear()->toDateString(),
                'otros' => ['voucher_servicio_id' => $servicioDemo->id],
            ]
        );

        VoucherBaseRelacion::updateOrCreate(
            ['external_id' => 'REL-BENEFICIARIO-PROF-CONSULTA-DEMO'],
            [
                'usuario_id' => $baseUsuario->id,
                'dependiente_id' => null,
                'profesional_id' => $baseProfesional->id,
                'laboratorio_id' => null,
                'servicio_id' => $baseServicio->id,
                'tipo_prestador' => 'profesional',
                'tipo_relacion' => 'beneficiario_profesional_servicio',
                'nivel_bono' => 'nivel_1',
                'estado' => 'vigente',
                'vigente_desde' => now()->subDay()->toDateString(),
                'vigente_hasta' => now()->addYear()->toDateString(),
                'requiere_auditoria' => false,
                'restricciones' => ['max_bonos_semana' => 3],
                'otros' => [
                    'voucher_profesional_id' => $profesionalDemo->id,
                    'voucher_servicio_id' => $servicioDemo->id,
                ],
            ]
        );
    }
}
