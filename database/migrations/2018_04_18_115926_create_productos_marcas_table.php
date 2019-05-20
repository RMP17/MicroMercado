<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductosMarcasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('productos_marcas', function (Blueprint $table) {
            $table->integer('id_producto')->unsigned();
            $table->foreign('id_producto')->references('id')->on('productos')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->integer('id_marca')->unsigned();
            $table->foreign('id_marca')->references('id')->on('marcas')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->primary(['id_producto', 'id_marca']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('productos_marcas');
    }
}
