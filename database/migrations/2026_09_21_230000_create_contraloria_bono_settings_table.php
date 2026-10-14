<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contraloria_bono_settings', function (Blueprint $table) {
            $table->id();
            $table->json('data');
            $table->timestamps();
        });

        DB::table('contraloria_bono_settings')->insert([
            'id' => 1,
            'data' => json_encode([
                'check_qr_integrity' => true,
                'check_closed_attention' => true,
                'check_professional_relation' => true,
                'check_medsdi_schedule' => true,
                'check_amount' => true,
                'check_duplicates' => true,
                'amount_tolerance' => 0,
                'max_charges_per_voucher' => 1,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('contraloria_bono_settings');
    }
};
