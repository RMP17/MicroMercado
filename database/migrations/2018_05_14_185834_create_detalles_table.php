<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDetallesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detalles', function (Blueprint $table) {
            $table->integer('id_compra_venta')->unsigned();
            $table->foreign('id_compra_venta')->references('id')->on('compras_ventas')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->integer('id_producto')->unsigned();
            $table->foreign('id_producto')->references('id')->on('productos')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->float('precio_unitario');
            $table->integer('cantidad_producto');
            $table->primary(['id_compra_venta', 'id_producto']);
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
        Schema::dropIfExists('detalles');
    }
}
