<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCierresCajasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cierres_cajas', function (Blueprint $table) {
            $table->increments('id');
            $table->date('fecha');
            $table->char('efectivo_inicial',10);
            $table->char('compras',10);
            $table->char('pagos_compras',10);
            $table->char('compras_nulas',10);
            $table->char('ventas',10);
            $table->char('pagos_ventas',10);
            $table->char('ventas_nulas',10);
            $table->char('depositos',10);
            $table->char('retiros',10);
            $table->char('efectivo_final',10);
            $table->integer('caja_id')->unsigned();
            $table->foreign('caja_id')->references('id')->on('cajas');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cierres_cajas');
    }
}
