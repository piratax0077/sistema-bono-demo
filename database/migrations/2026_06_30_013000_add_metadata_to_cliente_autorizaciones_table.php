<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMetadataToClienteAutorizacionesTable extends Migration
{
    public function up()
    {
        Schema::table('cliente_autorizaciones', function (Blueprint $table) {
            if (! Schema::hasColumn('cliente_autorizaciones', 'metadata')) {
                $table->json('metadata')->nullable()->after('ip_solicitante');
            }
        });
    }

    public function down()
    {
        Schema::table('cliente_autorizaciones', function (Blueprint $table) {
            if (Schema::hasColumn('cliente_autorizaciones', 'metadata')) {
                $table->dropColumn('metadata');
            }
        });
    }
}
