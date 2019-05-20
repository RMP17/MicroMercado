<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFacturasVentasMenoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('facturas_ventas_menores', function (Blueprint $table) {
            $table->increments('id');
            $table->char('nro_factura',20);
            $table->char('nro_autorizacion',20);
            $table->boolean('nulo')->default(false);
            $table->char('codigo_control',20)->nullable();
            $table->date('fecha');
            $table->double('total', 10);
            $table->double('descuento', 10);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('facturas_ventas_menores');
    }
}
