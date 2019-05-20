<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConfiguracionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('configuracion', function (Blueprint $table) {
            $table->increments('id');
            $table->char('nombre_super_mercado',100);
            $table->char('propietario_a',100);
            $table->char('casa_matriz',200);
            $table->char('telefono',10);
            $table->char('nit',15);
            $table->integer('numero_factura')->unsigned();
            $table->char('autorizacion',30);
            $table->char('dosificacion',255);
            $table->date('fecha_limite_emision');
            $table->tinyInteger('dias_antes_mostrar_vencimiento');
            $table->tinyInteger('stock_min_antes_mostrar');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('configuracion');
    }
}
