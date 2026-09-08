<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDatosClinicosToVouchersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::table('vouchers', function (Blueprint $table) {
    $table->string('mascota_nombre')->nullable()->after('mascota_id');
    $table->integer('mascota_edad')->nullable()->after('mascota_nombre');
    $table->string('mascota_raza')->nullable()->after('mascota_edad');

    $table->string('prestador_rut')->nullable()->after('profesional_id');
    $table->string('prestador_nombre')->nullable()->after('prestador_rut');
    $table->string('prestador_especialidad')->nullable()->after('prestador_nombre');
    $table->string('prestador_email')->nullable()->after('prestador_especialidad');
    $table->string('prestador_telefono')->nullable()->after('prestador_email');
    $table->string('prestador_direccion')->nullable()->after('prestador_telefono');

    $table->decimal('valor_total', 12, 2)->default(0)->after('valor');
});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
  public function down()
{
    Schema::table('vouchers', function (Blueprint $table) {
        $table->dropColumn([
            'mascota_nombre',
            'mascota_edad',
            'mascota_raza',
            'prestador_rut',
            'prestador_nombre',
            'prestador_especialidad',
            'prestador_email',
            'prestador_telefono',
            'prestador_direccion',
            'valor_total',
        ]);
    });
}
}
