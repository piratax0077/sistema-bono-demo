<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDuenoHashesToVoucherMascotasTable extends Migration
{
    public function up()
    {
        Schema::table('voucher_mascotas', function (Blueprint $table) {
            if (!Schema::hasColumn('voucher_mascotas', 'dueno_rut_hash')) {
                $table->string('dueno_rut_hash', 64)->nullable()->index();
            }

            if (!Schema::hasColumn('voucher_mascotas', 'dueno_telefono_hash')) {
                $table->string('dueno_telefono_hash', 64)->nullable()->index();
            }

            if (!Schema::hasColumn('voucher_mascotas', 'dueno_email_hash')) {
                $table->string('dueno_email_hash', 64)->nullable()->index();
            }
        });
    }

    public function down()
    {
        Schema::table('voucher_mascotas', function (Blueprint $table) {
            if (Schema::hasColumn('voucher_mascotas', 'dueno_rut_hash')) {
                $table->dropColumn('dueno_rut_hash');
            }

            if (Schema::hasColumn('voucher_mascotas', 'dueno_telefono_hash')) {
                $table->dropColumn('dueno_telefono_hash');
            }

            if (Schema::hasColumn('voucher_mascotas', 'dueno_email_hash')) {
                $table->dropColumn('dueno_email_hash');
            }
        });
    }
}
