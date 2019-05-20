<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateComprasVentasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('compras_ventas', function (Blueprint $table) {
            $table->increments('id');
            $table->dateTime('fecha_hora');
            $table->char('tipo',2);
            $table->boolean('pagada')->default(false);
            $table->float('total',10,2)->default(0);
            $table->float('descuento')->default(0);
            $table->float('efectivo')->default(0);
            $table->boolean('nulo')->default(false);
            $table->date('fecha_nulo')->nullable();
            $table->integer('empleado_id')->unsigned();
            $table->integer('caja_id')->unsigned();
            $table->foreign('empleado_id')->references('id')->on('personas')
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
        Schema::dropIfExists('compras_ventas');
    }
}
