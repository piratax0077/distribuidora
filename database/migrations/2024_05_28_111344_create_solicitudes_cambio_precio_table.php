<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSolicitudesCambioPrecioTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('solicitudes_cambio_precio', function (Blueprint $table) {
            $table->id();
            $table->integer('id_repuesto');
            $table->integer('id_usuario');
            $table->double('precio_anterior', 8, 2);
            $table->double('precio_nuevo', 8, 2);
            $table->string('motivo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('solicitudes_cambio_precio');
    }
}
