<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotaVentaEstadoHistorialTable extends Migration
{
    public function up()
    {
        Schema::create('nota_venta_estado_historial', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nota_venta_seguimiento_id');
            $table->string('tipo_estado', 30);
            $table->string('estado_anterior', 30)->nullable();
            $table->string('estado_nuevo', 30);
            $table->text('observacion')->nullable();
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->timestamps();

            $table->foreign('nota_venta_seguimiento_id', 'nv_hist_seguimiento_fk')
                ->references('id')->on('nota_venta_seguimientos')->onDelete('cascade');
            $table->index(['nota_venta_seguimiento_id', 'created_at'], 'nv_hist_fecha_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('nota_venta_estado_historial');
    }
}
