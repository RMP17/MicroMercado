<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductosCategoriasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('productos_categorias', function (Blueprint $table) {
            $table->integer('id_producto')->unsigned();
            $table->foreign('id_producto')->references('id')->on('productos')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->integer('id_categoria')->unsigned();
                $table->foreign('id_categoria')->references('id')->on('categorias')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->primary(['id_producto', 'id_categoria']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('productos_categorias');
    }
}
