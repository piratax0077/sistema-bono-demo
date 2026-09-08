<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $schema = Schema::connection('medichile');
        if (app()->environment('testing')) {
            $schema->dropIfExists('horas_medicas');
            $schema->dropIfExists('profesionales');
            $schema->dropIfExists('pacientes');
        }
        if (! $schema->hasTable('pacientes')) {
            $schema->create('pacientes', function (Blueprint $t) { $t->id(); $t->string('rut',20)->unique(); $t->string('nombre'); $t->string('email')->nullable(); $t->string('telefono')->nullable(); $t->timestamps(); });
        }
        if (! $schema->hasTable('profesionales')) {
            $schema->create('profesionales', function (Blueprint $t) { $t->id(); $t->string('rut',20)->unique(); $t->string('nombre'); $t->string('apellido_uno')->nullable(); $t->string('apellido_dos')->nullable(); $t->string('sexo',5)->nullable(); $t->string('email')->nullable(); $t->string('telefono_uno')->nullable(); $t->boolean('estado')->default(true); $t->boolean('certificado')->default(true); $t->unsignedBigInteger('id_tipo_atencion')->nullable(); $t->timestamps(); });
        }
        if (! $schema->hasTable('horas_medicas')) {
            $schema->create('horas_medicas', function (Blueprint $t) { $t->id(); $t->date('fecha_consulta'); $t->time('hora_inicio'); $t->time('hora_termino'); $t->string('descripcion')->nullable()->index(); $t->string('observaciones')->nullable(); $t->unsignedBigInteger('id_profesional')->index(); $t->unsignedBigInteger('id_paciente')->index(); $t->unsignedSmallInteger('id_estado')->default(2); $t->timestamp('fecha_realizacion_consulta')->nullable(); $t->timestamps(); });
        }
    }
    public function down(): void { Schema::connection('medichile')->dropIfExists('horas_medicas'); Schema::connection('medichile')->dropIfExists('profesionales'); Schema::connection('medichile')->dropIfExists('pacientes'); }
};
