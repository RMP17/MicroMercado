<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMovimientosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('movimientos', function (Blueprint $table) {
            $table->integer('id')->unsigned();
            $table->integer('id_cuenta')->unsigned();
            $table->foreign('id_cuenta')->references('id_persona')->on('cuentas')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->integer('id_caja')->unsigned();
            $table->foreign('id_caja')->references('id')->on('cajas')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->primary(['id', 'id_cuenta', 'id_caja'],'id_cuenta_caja');
            $table->float('monto',10, 2);
            $table->dateTime('fecha_hora');
            $table->char('tipo',1);
        });
        DB::statement('ALTER TABLE movimientos MODIFY id INTEGER NOT NULL AUTO_INCREMENT');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('movimientos');
    }
}
