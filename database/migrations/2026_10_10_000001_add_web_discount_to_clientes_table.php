<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWebDiscountToClientesTable extends Migration
{
    public function up()
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->boolean('descuento_web')->default(false)->after('porcentaje');
            $table->decimal('porcentaje_web', 5, 2)->default(0)->after('descuento_web');
        });
    }

    public function down()
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn(['descuento_web', 'porcentaje_web']);
        });
    }
}
