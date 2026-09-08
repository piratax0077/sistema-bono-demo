<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOtpFieldsToVouchersTable extends Migration
{
    public function up()
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->string('otp_hash')->nullable()->after('qr_usado_at');
            $table->timestamp('otp_expira')->nullable()->after('otp_hash');
            $table->timestamp('otp_validado_at')->nullable()->after('otp_expira');
        });
    }

    public function down()
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropColumn([
                'otp_hash',
                'otp_expira',
                'otp_validado_at',
            ]);
        });
    }
}
