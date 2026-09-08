<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInvalidacionFieldsToVouchersTable extends Migration
{
    public function up()
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->timestamp('invalidado_en')->nullable()->after('qr_usado_at');
            $table->text('motivo_invalidacion')->nullable()->after('invalidado_en');
            $table->boolean('copago_devuelto')->default(false)->after('motivo_invalidacion');
        });
    }

    public function down()
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropColumn([
                'invalidado_en',
                'motivo_invalidacion',
                'copago_devuelto',
            ]);
        });
    }
}
