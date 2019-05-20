<?php

use Illuminate\Database\Seeder;
use App\Venta;

class ComprasVentasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $comprasVentas = factory(App\CompraVenta::class, 6000)->create();
        foreach ($comprasVentas as $compraVenta) {
            $venta = new Venta();
            $venta->venta_menor = (bool)random_int(0, 1);
            $venta->cliente_id = random_int(3, 25);
            $compraVenta->ventas()->save($venta);
        }
    }
}
