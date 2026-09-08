<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $schema = Schema::connection('personas_fast');
        if (app()->environment('testing')) {
            $schema->dropIfExists('personas_rapidas_ediciones');
            $schema->dropIfExists('personas');
        }
        $fullTextDisponible = DB::connection('personas_fast')->getDriverName() !== 'sqlite';

        if (! $schema->hasTable('personas')) {
            $schema->create('personas', function (Blueprint $table) use ($fullTextDisponible) {
            $table->bigIncrements('id');
            $table->string('rut_original', 20);
            $table->string('rut_normalizado', 20)->unique();
            $table->unsignedBigInteger('rut_cuerpo')->index();
            $table->char('rut_dv', 1);
            $table->string('nombre1')->nullable();
            $table->string('appaterno')->nullable();
            $table->string('apmaterno')->nullable();
            $table->string('nombre_completo')->index();
            $table->string('estado', 30)->default('activo');
            $table->timestamps();
            if ($fullTextDisponible) {
                $table->fullText('nombre_completo');
            }
            });
        }
        if (! $schema->hasTable('personas_rapidas_ediciones')) {
            $schema->create('personas_rapidas_ediciones', function (Blueprint $table) {
            $table->id(); $table->string('rut_normalizado', 20)->unique();
            $table->string('nombre1')->nullable(); $table->string('appaterno')->nullable(); $table->string('apmaterno')->nullable();
            $table->string('nombre_completo')->nullable(); $table->string('email')->nullable(); $table->string('telefono', 50)->nullable();
            $table->text('direccion_encrypted')->nullable(); $table->string('origen')->nullable(); $table->timestamps();
            });
        }
    }
    public function down(): void
    {
        Schema::connection('personas_fast')->dropIfExists('personas_rapidas_ediciones');
        Schema::connection('personas_fast')->dropIfExists('personas');
    }
};
