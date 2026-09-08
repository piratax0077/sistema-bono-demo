<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVoucherDeliveryRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('voucher_delivery_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_id')->constrained('vouchers')->cascadeOnDelete();
            $table->unsignedBigInteger('cliente_user_id')->nullable()->index();
            $table->string('canal', 60)->index();
            $table->string('destino_tipo', 80)->index();
            $table->string('destino', 180)->nullable();
            $table->string('estado', 40)->default('prepared')->index();
            $table->text('mensaje')->nullable();
            $table->text('action_url')->nullable();
            $table->timestamp('enviado_en')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['estado', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('voucher_delivery_requests');
    }
}
