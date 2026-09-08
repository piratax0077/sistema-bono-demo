<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSecurityFieldsToVouchersTable extends Migration
{
    public function up()
    {
        Schema::table('vouchers', function (Blueprint $table) {

            $table->string('qr_firma')->nullable();

            $table->timestamp('qr_expira')->nullable();

            $table->boolean('qr_usado')->default(false);

            $table->timestamp('qr_usado_at')->nullable();

        });
    }

    public function down()
    {
        Schema::table('vouchers', function (Blueprint $table) {

            $table->dropColumn([
                'qr_firma',
                'qr_expira',
                'qr_usado',
                'qr_usado_at'
            ]);

        });
    }
}
