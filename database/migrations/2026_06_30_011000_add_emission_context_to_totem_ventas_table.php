<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEmissionContextToTotemVentasTable extends Migration
{
    public function up()
    {
        Schema::table('totem_ventas', function (Blueprint $table) {
            if (!Schema::hasColumn('totem_ventas', 'comprador_nombre')) {
                $table->string('comprador_nombre')->nullable()->after('cliente_email');
            }

            if (!Schema::hasColumn('totem_ventas', 'comprador_telefono')) {
                $table->string('comprador_telefono', 50)->nullable()->after('comprador_nombre');
            }

            if (!Schema::hasColumn('totem_ventas', 'office_number')) {
                $table->string('office_number', 80)->nullable()->after('comprador_telefono');
            }

            if (!Schema::hasColumn('totem_ventas', 'emision_lat')) {
                $table->decimal('emision_lat', 10, 7)->nullable()->after('office_number');
            }

            if (!Schema::hasColumn('totem_ventas', 'emision_lng')) {
                $table->decimal('emision_lng', 10, 7)->nullable()->after('emision_lat');
            }

            if (!Schema::hasColumn('totem_ventas', 'client_ip')) {
                $table->string('client_ip', 45)->nullable()->after('emision_lng');
            }

            if (!Schema::hasColumn('totem_ventas', 'metadata')) {
                $table->json('metadata')->nullable()->after('client_ip');
            }
        });
    }

    public function down()
    {
        Schema::table('totem_ventas', function (Blueprint $table) {
            foreach ([
                'metadata',
                'client_ip',
                'emision_lng',
                'emision_lat',
                'office_number',
                'comprador_telefono',
                'comprador_nombre',
            ] as $column) {
                if (Schema::hasColumn('totem_ventas', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
