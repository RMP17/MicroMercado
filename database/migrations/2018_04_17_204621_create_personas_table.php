<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePersonasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('personas', function (Blueprint $table) {
            $table->increments('id');
            $table->char('ci',15);
            $table->char('nombre',50);
            $table->char('telefono',10);
            $table->string('direccion',100);
            $table->boolean('cliente')->default(false);
            $table->boolean('proveedor')->default(false);
            $table->boolean('empleado')->default(false);
        });
        DB::update("ALTER TABLE personas AUTO_INCREMENT = 3;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('personas');
    }
}
