<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotaVentaSeguimientosTable extends Migration
{
    public function up()
    {
        Schema::create('nota_venta_seguimientos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('numero_nota_venta')->unique();
            $table->unsignedBigInteger('numero_solicitud_web')->nullable()->index();
            $table->unsignedBigInteger('usuario_web_id')->nullable()->index();
            $table->unsignedBigInteger('cliente_id')->nullable()->index();
            $table->string('estado_pedido', 30)->default('recibido')->index();
            $table->string('estado_pago', 30)->default('pendiente')->index();
            $table->string('estado_entrega', 30)->default('pendiente')->index();
            $table->string('tipo_entrega', 30)->nullable();
            $table->string('transportista', 100)->nullable();
            $table->string('numero_seguimiento', 150)->nullable();
            $table->text('observacion_cliente')->nullable();
            $table->text('observacion_interna')->nullable();
            $table->timestamp('confirmado_at')->nullable();
            $table->timestamp('preparando_at')->nullable();
            $table->timestamp('despachado_at')->nullable();
            $table->timestamp('entregado_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('nota_venta_seguimientos');
    }
}
