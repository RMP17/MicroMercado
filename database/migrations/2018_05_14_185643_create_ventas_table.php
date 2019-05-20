<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVentasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->integer('id_compra_venta')->unsigned();
            $table->foreign('id_compra_venta')->references('id')->on('compras_ventas')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->primary('id_compra_venta');
            $table->boolean('venta_menor')->default(false);
            $table->integer('factura_venta_menor_id')->unsigned()->nullable();
            $table->integer('cliente_id')->unsigned()->nullable();
            $table->foreign('cliente_id')->references('id')->on('personas')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ventas');
    }
}
