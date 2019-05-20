<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->increments('id');
            $table->char('descripcion',50);
            $table->char('tipo_unidad',15);
            $table->integer('stock');
            $table->float('precio_compra_unidad');
            $table->float('precio_venta_unidad');
            $table->date('fecha_caducidad')->nullable();
            $table->integer('cantidad_almacen');
            $table->date('fecha_caducidad_almacen')->nullable();
            $table->boolean('notificar')->default(true);
            $table->boolean('notificar_fecha_caducidad')->default(true);
            $table->char('img',15)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('productos');
    }
}
