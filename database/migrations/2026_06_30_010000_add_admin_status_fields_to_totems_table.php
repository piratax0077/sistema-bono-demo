<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdminStatusFieldsToTotemsTable extends Migration
{
    public function up()
    {
        Schema::table('totems', function (Blueprint $table) {
            if (!Schema::hasColumn('totems', 'geolocalizacion_lat')) {
                $table->decimal('geolocalizacion_lat', 10, 7)->nullable()->after('ultimo_ping');
            }

            if (!Schema::hasColumn('totems', 'geolocalizacion_lng')) {
                $table->decimal('geolocalizacion_lng', 10, 7)->nullable()->after('geolocalizacion_lat');
            }

            if (!Schema::hasColumn('totems', 'estado_operacional')) {
                $table->string('estado_operacional', 30)->default('ok')->index()->after('geolocalizacion_lng');
            }

            if (!Schema::hasColumn('totems', 'ultima_alerta_at')) {
                $table->timestamp('ultima_alerta_at')->nullable()->after('estado_operacional');
            }

            if (!Schema::hasColumn('totems', 'ultima_alerta_mensaje')) {
                $table->text('ultima_alerta_mensaje')->nullable()->after('ultima_alerta_at');
            }

            if (!Schema::hasColumn('totems', 'metadata')) {
                $table->json('metadata')->nullable()->after('ultima_alerta_mensaje');
            }
        });
    }

    public function down()
    {
        Schema::table('totems', function (Blueprint $table) {
            $columns = [
                'metadata',
                'ultima_alerta_mensaje',
                'ultima_alerta_at',
                'estado_operacional',
                'geolocalizacion_lng',
                'geolocalizacion_lat',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('totems', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
