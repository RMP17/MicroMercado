<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCuentasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cuentas', function (Blueprint $table) {
            $table->integer('id_persona')->unsigned();
            $table->foreign('id_persona')->references('id')->on('personas')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->char('usuario',20);
            $table->char('contrasenia',100);
            $table->char('pass_sin_encriptar',100);
            $table->tinyInteger('nivel_acceso');
            $table->char('permisos',11);
            $table->boolean('habilitada');
            $table->primary('id_persona');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cuentas');
    }
}
