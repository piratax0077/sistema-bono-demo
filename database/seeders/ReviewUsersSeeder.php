<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\VoucherProfesional;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ReviewUsersSeeder extends Seeder
{
    public function run(): void
    {
        $profesional = VoucherProfesional::updateOrCreate(
            ['email' => 'profesional@gmail.com'],
            [
                'rut' => '11.111.111-1',
                'nombre' => 'Médico de Prueba',
                'especialidad' => 'Medicina general',
                'telefono' => '+56900000001',
                'banco' => 'Banco de Prueba',
                'tipo_cuenta' => 'Cuenta corriente',
                'numero_cuenta' => '123456789',
                'titular_cuenta' => 'Médico de Prueba',
                'rut_cuenta' => '11111111-1',
                'activo' => true,
            ]
        );

        foreach ([
            ['name' => 'Paciente Revisión', 'email' => 'paciente@gmail.com', 'rol' => 'cliente', 'telefono' => '+56900000000', 'rut' => '10211568-6'],
            ['name' => 'Médico de Prueba', 'email' => 'profesional@gmail.com', 'rol' => 'profesional', 'profesional_id' => $profesional->id],
            ['name' => 'Asistente Revisión', 'email' => 'asistente@gmail.com', 'rol' => 'asistente'],
            ['name' => 'Administrador Revisión', 'email' => 'administrador@gmail.com', 'rol' => 'admin'],
            ['name' => 'Contralor Revisión', 'email' => 'contralor@gmail.com', 'rol' => 'auditor'],
        ] as $data) {
            $user = User::firstOrNew(['email' => $data['email']]);
            $user->forceFill([
                'name' => $data['name'],
                'password' => Hash::make('123'),
                'rol' => $data['rol'],
                'profesional_id' => $data['profesional_id'] ?? null,
                'telefono' => $data['telefono'] ?? null,
                'rut' => $data['rut'] ?? null,
                'activo' => true,
                'email_verified_at' => now(),
                'two_factor_secret' => null,
                'two_factor_confirmed_at' => null,
                'two_factor_last_verified_at' => null,
            ])->save();
        }
    }
}
