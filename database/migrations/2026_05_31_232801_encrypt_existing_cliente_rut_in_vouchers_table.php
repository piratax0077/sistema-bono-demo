<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

class EncryptExistingClienteRutInVouchersTable extends Migration
{
    public function up()
    {
        DB::table('vouchers')
            ->whereNotNull('cliente_rut')
            ->orderBy('id')
            ->chunk(100, function ($vouchers) {

                foreach ($vouchers as $voucher) {

                    try {
                        Crypt::decryptString($voucher->cliente_rut);
                        continue;
                    } catch (\Exception $e) {
                        // No estaba cifrado, seguimos
                    }

                    $rutNormalizado = strtoupper(trim($voucher->cliente_rut));

                    DB::table('vouchers')
                        ->where('id', $voucher->id)
                        ->update([
                            'cliente_rut' => Crypt::encryptString($rutNormalizado),
                            'cliente_rut_hash' => hash('sha256', $rutNormalizado),
                        ]);
                }
            });
    }

    public function down()
    {
        // No revertimos por seguridad
    }
}
