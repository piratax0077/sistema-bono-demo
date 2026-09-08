<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherBaseUsuario;
use App\Models\VoucherProfesional;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Carbon;

class CompletarDatosQrDemoSeeder extends Seeder
{
    public function run(): void
    {
        $paciente = User::where('email', 'paciente@gmail.com')->firstOrFail();
        $profesionalUser = User::where('email', 'profesional@gmail.com')->firstOrFail();
        $profesional = VoucherProfesional::findOrFail($profesionalUser->profesional_id);
        $rut = strtoupper(preg_replace('/[^0-9K]/i', '', (string) $paciente->rut));
        $direccion = 'Av. Providencia 1234, Santiago';
        $fechaNacimiento = '1990-05-15';

        VoucherBaseUsuario::where('external_id', 'DEMO-PACIENTE-REVISION')->update([
            'rut_hash' => hash_hmac('sha256', $rut, (string) config('app.key')),
            'rut_sha256' => hash('sha256', $rut),
            'rut_encrypted' => Crypt::encryptString($rut),
            'direccion_encrypted' => Crypt::encryptString($direccion),
            'fecha_nacimiento_encrypted' => Crypt::encryptString($fechaNacimiento),
            'otros' => json_encode(['grupo_ingreso' => 'A']),
        ]);

        Voucher::where('codigo', 'DEMO-FLUJO-001')->update([
            'cliente_rut' => Crypt::encryptString($rut),
            'cliente_rut_hash' => hash('sha256', $rut),
            'beneficiario_nombre' => $paciente->name,
            'beneficiario_rut' => Crypt::encryptString($rut),
            'beneficiario_rut_hash' => hash('sha256', $rut),
            'beneficiario_parentesco' => 'Titular',
            'beneficiario_direccion' => Crypt::encryptString($direccion),
            'beneficiario_fecha_nacimiento' => Crypt::encryptString($fechaNacimiento),
            'beneficiario_edad' => Carbon::parse($fechaNacimiento)->age,
            'prestador_rut' => $profesional->rut,
        ]);
    }
}
