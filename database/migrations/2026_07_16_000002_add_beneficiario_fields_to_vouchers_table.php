<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBeneficiarioFieldsToVouchersTable extends Migration
{
    public function up()
    {
        Schema::table('vouchers', function (Blueprint $table) {
            if (! Schema::hasColumn('vouchers', 'beneficiario_tipo')) {
                $table->string('beneficiario_tipo', 20)
                    ->default('titular')
                    ->after('cliente_email');
            }

            if (! Schema::hasColumn('vouchers', 'beneficiario_base_usuario_id')) {
                $table->unsignedBigInteger('beneficiario_base_usuario_id')
                    ->nullable()
                    ->index()
                    ->after('beneficiario_tipo');
            }

            if (! Schema::hasColumn('vouchers', 'beneficiario_dependiente_id')) {
                $table->unsignedBigInteger('beneficiario_dependiente_id')
                    ->nullable()
                    ->index()
                    ->after('beneficiario_base_usuario_id');
            }

            if (! Schema::hasColumn('vouchers', 'beneficiario_nombre')) {
                $table->string('beneficiario_nombre', 150)
                    ->nullable()
                    ->after('beneficiario_dependiente_id');
            }

            if (! Schema::hasColumn('vouchers', 'beneficiario_rut')) {
                $table->text('beneficiario_rut')
                    ->nullable()
                    ->after('beneficiario_nombre');
            }

            if (! Schema::hasColumn('vouchers', 'beneficiario_rut_hash')) {
                $table->string('beneficiario_rut_hash', 64)
                    ->nullable()
                    ->index()
                    ->after('beneficiario_rut');
            }

            if (! Schema::hasColumn('vouchers', 'beneficiario_parentesco')) {
                $table->string('beneficiario_parentesco', 80)
                    ->nullable()
                    ->after('beneficiario_rut_hash');
            }

            if (! Schema::hasColumn('vouchers', 'beneficiario_direccion')) {
                $table->text('beneficiario_direccion')
                    ->nullable()
                    ->after('beneficiario_parentesco');
            }

            if (! Schema::hasColumn('vouchers', 'beneficiario_fecha_nacimiento')) {
                $table->text('beneficiario_fecha_nacimiento')
                    ->nullable()
                    ->after('beneficiario_direccion');
            }

            if (! Schema::hasColumn('vouchers', 'beneficiario_edad')) {
                $table->unsignedSmallInteger('beneficiario_edad')
                    ->nullable()
                    ->after('beneficiario_fecha_nacimiento');
            }
        });
    }

    public function down()
    {
        Schema::table('vouchers', function (Blueprint $table) {
            foreach ([
                'beneficiario_edad',
                'beneficiario_fecha_nacimiento',
                'beneficiario_direccion',
                'beneficiario_parentesco',
                'beneficiario_rut_hash',
                'beneficiario_rut',
                'beneficiario_nombre',
                'beneficiario_dependiente_id',
                'beneficiario_base_usuario_id',
                'beneficiario_tipo',
            ] as $column) {
                if (Schema::hasColumn('vouchers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
