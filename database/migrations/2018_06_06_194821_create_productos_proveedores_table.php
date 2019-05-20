<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductosProveedoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('productos_proveedores', function (Blueprint $table) {
            $table->integer('id_producto')->unsigned();
            $table->foreign('id_producto')->references('id')->on('productos')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->integer('id_proveedor')->unsigned();
            $table->foreign('id_proveedor')->references('id')->on('personas')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->primary(['id_producto', 'id_proveedor']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('productos_proveedores');
    }
}
