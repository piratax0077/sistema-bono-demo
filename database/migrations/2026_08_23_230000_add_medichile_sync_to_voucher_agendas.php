<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('voucher_agendas', function (Blueprint $table) {
            $table->unsignedBigInteger('medichile_hora_medica_id')->nullable()->index()->after('observacion');
            $table->unsignedBigInteger('medichile_estado_id')->nullable()->after('medichile_hora_medica_id');
            $table->timestamp('medichile_sincronizado_at')->nullable()->after('medichile_estado_id');
            $table->string('medichile_sync_error')->nullable()->after('medichile_sincronizado_at');
        });
    }

    public function down(): void
    {
        Schema::table('voucher_agendas', function (Blueprint $table) {
            $table->dropIndex(['medichile_hora_medica_id']);
            $table->dropColumn([
                'medichile_hora_medica_id',
                'medichile_estado_id',
                'medichile_sincronizado_at',
                'medichile_sync_error',
            ]);
        });
    }
};
