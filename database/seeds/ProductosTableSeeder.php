<?php

use Illuminate\Database\Seeder;
use App\Marca;
use App\Categoria;
use App\Codigo;
use App\Producto;
use App\Persona;

class ProductosTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * pil andina de chuquisaca
     * @return void
     */
    public function run()
    {

        factory(Persona::class)->times(20)->create();
        factory(Producto::class)->times(20)->create();
        factory(Codigo::class)->times(10)->create();
        factory(Marca::class)->times(10)->create();
        factory(Categoria::class)->times(5)->create();

        $marcas = Marca::all();
        $categorias = Categoria::all();
        $codigos = Codigo::all();
        $productos = Producto::all();
        foreach ($productos as $producto) {
            $marca = $marcas->random();
            $categoria = $categorias->random();
            $codigo = $codigos->random();

            $producto->marcas()->attach($marca);
            $producto->categorias()->attach($categoria);
            $codigo->producto_id = $producto->id;
            $codigo->save();
        }

    }
}
