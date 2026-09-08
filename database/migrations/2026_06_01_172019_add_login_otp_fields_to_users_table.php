<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLoginOtpFieldsToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('login_otp_hash')->nullable();
            $table->timestamp('login_otp_expira')->nullable();
            $table->timestamp('login_otp_validado_at')->nullable();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'login_otp_hash',
                'login_otp_expira',
                'login_otp_validado_at',
            ]);
        });
    }
}
