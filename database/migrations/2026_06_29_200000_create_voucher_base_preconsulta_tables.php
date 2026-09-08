<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVoucherBasePreconsultaTables extends Migration
{
    public function up()
    {
        Schema::create('voucher_base_usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('external_id', 100)->nullable()->unique();
            $table->string('nombre');
            $table->string('rut_hash', 64)->unique();
            $table->string('rut_sha256', 64)->nullable()->unique();
            $table->text('rut_encrypted');
            $table->text('direccion_encrypted')->nullable();
            $table->text('fecha_nacimiento_encrypted')->nullable();
            $table->json('otros')->nullable();
            $table->string('estado', 30)->default('activo')->index();
            $table->date('vigente_desde')->nullable();
            $table->date('vigente_hasta')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('voucher_base_dependientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')
                ->constrained('voucher_base_usuarios')
                ->cascadeOnDelete();
            $table->string('external_id', 100)->nullable()->unique();
            $table->string('nombre');
            $table->string('rut_hash', 64)->unique();
            $table->string('rut_sha256', 64)->nullable()->unique();
            $table->text('rut_encrypted');
            $table->text('direccion_encrypted')->nullable();
            $table->text('fecha_nacimiento_encrypted')->nullable();
            $table->string('parentesco', 80)->nullable();
            $table->json('otros')->nullable();
            $table->string('estado', 30)->default('activo')->index();
            $table->date('vigente_desde')->nullable();
            $table->date('vigente_hasta')->nullable()->index();
            $table->timestamps();

            $table->index(['usuario_id', 'estado']);
        });

        Schema::create('voucher_base_profesionales', function (Blueprint $table) {
            $table->id();
            $table->string('external_id', 100)->nullable()->unique();
            $table->string('nombre');
            $table->string('rut_hash', 64)->unique();
            $table->string('rut_sha256', 64)->nullable()->unique();
            $table->text('rut_encrypted');
            $table->text('direccion_encrypted')->nullable();
            $table->text('fecha_nacimiento_encrypted')->nullable();
            $table->string('profesion', 120)->nullable()->index();
            $table->string('especialidad', 150)->nullable()->index();
            $table->string('nivel_bono', 40)->nullable()->index();
            $table->string('email')->nullable();
            $table->string('telefono', 50)->nullable();
            $table->json('otros')->nullable();
            $table->string('estado', 30)->default('activo')->index();
            $table->date('vigente_desde')->nullable();
            $table->date('vigente_hasta')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('voucher_base_laboratorios', function (Blueprint $table) {
            $table->id();
            $table->string('external_id', 100)->nullable()->unique();
            $table->string('nombre');
            $table->string('rut_hash', 64)->unique();
            $table->string('rut_sha256', 64)->nullable()->unique();
            $table->text('rut_encrypted');
            $table->text('direccion_encrypted')->nullable();
            $table->string('tipo_laboratorio', 80)->nullable()->index();
            $table->string('especialidad', 150)->nullable()->index();
            $table->string('nivel_bono', 40)->nullable()->index();
            $table->string('email')->nullable();
            $table->string('telefono', 50)->nullable();
            $table->json('otros')->nullable();
            $table->string('estado', 30)->default('activo')->index();
            $table->date('vigente_desde')->nullable();
            $table->date('vigente_hasta')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('voucher_base_servicios', function (Blueprint $table) {
            $table->id();
            $table->string('external_id', 100)->nullable()->unique();
            $table->string('codigo', 80)->unique();
            $table->string('nombre');
            $table->string('tipo_servicio', 80)->nullable()->index();
            $table->string('especialidad', 150)->nullable()->index();
            $table->string('nivel_bono', 40)->nullable()->index();
            $table->unsignedBigInteger('valor_referencial')->nullable();
            $table->json('otros')->nullable();
            $table->string('estado', 30)->default('activo')->index();
            $table->date('vigente_desde')->nullable();
            $table->date('vigente_hasta')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('voucher_base_relaciones', function (Blueprint $table) {
            $table->id();
            $table->string('external_id', 100)->nullable()->unique();
            $table->foreignId('usuario_id')
                ->constrained('voucher_base_usuarios')
                ->cascadeOnDelete();
            $table->foreignId('dependiente_id')->nullable()
                ->constrained('voucher_base_dependientes')
                ->cascadeOnDelete();
            $table->foreignId('profesional_id')->nullable()
                ->constrained('voucher_base_profesionales')
                ->nullOnDelete();
            $table->foreignId('laboratorio_id')->nullable()
                ->constrained('voucher_base_laboratorios')
                ->nullOnDelete();
            $table->foreignId('servicio_id')->nullable()
                ->constrained('voucher_base_servicios')
                ->nullOnDelete();
            $table->string('tipo_prestador', 30)->nullable()->index();
            $table->string('tipo_relacion', 80)->default('autorizacion_previa')->index();
            $table->string('nivel_bono', 40)->nullable()->index();
            $table->string('estado', 30)->default('vigente')->index();
            $table->date('vigente_desde')->nullable();
            $table->date('vigente_hasta')->nullable()->index();
            $table->boolean('requiere_auditoria')->default(false)->index();
            $table->string('motivo_auditoria')->nullable();
            $table->json('restricciones')->nullable();
            $table->json('otros')->nullable();
            $table->timestamps();

            $table->index(['usuario_id', 'dependiente_id', 'estado']);
            $table->index(['profesional_id', 'servicio_id', 'estado']);
            $table->index(['laboratorio_id', 'servicio_id', 'estado']);
        });

        Schema::create('voucher_preconsultas', function (Blueprint $table) {
            $table->id();
            $table->string('external_consulta_id', 100)->nullable()->unique();
            $table->foreignId('usuario_id')->nullable()
                ->constrained('voucher_base_usuarios')
                ->nullOnDelete();
            $table->foreignId('dependiente_id')->nullable()
                ->constrained('voucher_base_dependientes')
                ->nullOnDelete();
            $table->foreignId('profesional_id')->nullable()
                ->constrained('voucher_base_profesionales')
                ->nullOnDelete();
            $table->foreignId('laboratorio_id')->nullable()
                ->constrained('voucher_base_laboratorios')
                ->nullOnDelete();
            $table->foreignId('servicio_id')->nullable()
                ->constrained('voucher_base_servicios')
                ->nullOnDelete();
            $table->foreignId('relacion_autorizada_id')->nullable()
                ->constrained('voucher_base_relaciones')
                ->nullOnDelete();
            $table->foreignId('voucher_id')->nullable()
                ->constrained('vouchers')
                ->nullOnDelete();
            $table->string('usuario_consulta_tipo', 30)->default('titular')->index();
            $table->string('usuario_rut_hash', 64)->nullable()->index();
            $table->string('usuario_rut_sha256', 64)->nullable()->index();
            $table->string('prestador_tipo', 30)->nullable()->index();
            $table->string('prestador_rut_hash', 64)->nullable()->index();
            $table->string('prestador_rut_sha256', 64)->nullable()->index();
            $table->string('hash_validacion_modo', 30)->default('hmac_sha256')->index();
            $table->decimal('geolocalizacion_lat', 10, 7)->nullable();
            $table->decimal('geolocalizacion_lng', 10, 7)->nullable();
            $table->timestamp('fecha_consulta')->nullable()->index();
            $table->timestamp('hora_respuesta')->nullable();
            $table->string('token_hash', 64)->nullable()->unique();
            $table->timestamp('token_expira_at')->nullable()->index();
            $table->timestamp('token_consumido_at')->nullable();
            $table->string('resultado', 40)->default('pendiente')->index();
            $table->text('motivo')->nullable();
            $table->string('codigo_voucher_externo', 100)->nullable();
            $table->string('voucher_generado_codigo', 100)->nullable();
            $table->boolean('guardar_detalle')->default(true);
            $table->string('request_fingerprint', 64)->nullable()->index();
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent_hash', 64)->nullable();
            $table->timestamps();

            $table->index(['resultado', 'created_at']);
            $table->index(['usuario_rut_hash', 'resultado']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('voucher_preconsultas');
        Schema::dropIfExists('voucher_base_relaciones');
        Schema::dropIfExists('voucher_base_servicios');
        Schema::dropIfExists('voucher_base_laboratorios');
        Schema::dropIfExists('voucher_base_profesionales');
        Schema::dropIfExists('voucher_base_dependientes');
        Schema::dropIfExists('voucher_base_usuarios');
    }
}
