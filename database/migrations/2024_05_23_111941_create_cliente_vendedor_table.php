<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClienteVendedorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cliente_vendedor', function (Blueprint $table) {
            $table->id();
            $table->integer('id_cliente');
            $table->integer('id_vendedor');
            $table->integer('activo');
            $table->date('fecha_asignacion');
            $table->date('fecha_desasignacion')->nullable();
            $table->integer('id_usuario');
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
        Schema::dropIfExists('cliente_vendedor');
    }
}
