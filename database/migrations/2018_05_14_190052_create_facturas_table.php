<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFacturasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('facturas', function (Blueprint $table) {
            $table->integer('id_compra_venta')->unsigned();
            $table->foreign('id_compra_venta')->references('id')->on('compras_ventas')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->primary('id_compra_venta');
            $table->char('nro_factura',20);
            $table->char('nro_autorizacion',20);
            $table->boolean('nulo')->default(false);
            $table->char('codigo_control',20)->nullable();
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
        Schema::dropIfExists('facturas');
    }
}
