<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDatosClinicosToTotemVentasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::table('totem_ventas', function (Blueprint $table) {
    $table->string('mascota_nombre')->nullable()->after('cliente_email');
    $table->integer('mascota_edad')->nullable()->after('mascota_nombre');
    $table->string('mascota_raza')->nullable()->after('mascota_edad');

    $table->string('prestador_rut')->nullable()->after('prestador_nombre');
    $table->string('prestador_especialidad')->nullable()->after('prestador_rut');
    $table->string('prestador_email')->nullable()->after('prestador_especialidad');
    $table->string('prestador_telefono')->nullable()->after('prestador_email');
    $table->string('prestador_direccion')->nullable()->after('prestador_telefono');

    $table->decimal('valor_total', 12, 2)->default(0)->after('total');
    $table->decimal('copago_seguro', 12, 2)->default(0)->after('valor_total');
    $table->decimal('copago_cliente', 12, 2)->default(0)->after('copago_seguro');
});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('totem_ventas', function (Blueprint $table) {
            //
        });
    }
}
