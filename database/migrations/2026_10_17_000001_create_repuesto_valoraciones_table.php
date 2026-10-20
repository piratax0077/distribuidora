<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRepuestoValoracionesTable extends Migration
{
    public function up()
    {
        Schema::create('repuesto_valoraciones', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('repuesto_id')->index();
            $table->unsignedBigInteger('usuario_id')->index();
            $table->unsignedTinyInteger('valoracion');
            $table->timestamps();
            $table->unique(['repuesto_id', 'usuario_id'], 'repuesto_valoracion_usuario_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('repuesto_valoraciones');
    }
}
