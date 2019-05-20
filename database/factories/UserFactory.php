<?php

use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

/*$factory->define(App\User::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
        'remember_token' => str_random(10),
    ];
});*/

$factory->define(App\Marca::class, function (Faker $faker){
   return [
     'nombre' => $faker->name
   ];
});
$factory->define(App\Categoria::class, function (Faker $faker){
   return [
     'descripcion' => $faker->lastName
   ];
});
$factory->define(App\Codigo::class, function (Faker $faker){
   return [
     'codigo_barra' => $faker->unique()->ean13
   ];
});
$factory->define(App\Producto::class, function (Faker $faker){
   return [
     'descripcion' => str_random(5),
     'tipo_unidad' => str_random(2),
     'stock'       => $faker->randomNumber(4,false),
     'costo_unitario' => $faker->randomFloat(2,0, 1000),
     'fecha_caducidad' => $faker->dateTimeBetween('0 years','2 years'),
     'cantidad_almacen' => $faker->randomNumber(3,false),
     'fecha_caducidad_almacen' => $faker->dateTimeBetween('0 years','2 years'),
   ];
});
$factory->define(App\Persona::class, function (Faker $faker){
   return [
     'ci' => $faker->randomNumber(8,true),
     'nombre' => $faker->name,
     'apellido1'       => $faker->lastName,
     'apellido2'       => $faker->firstName,
     'telefono' => $faker->randomNumber(8,true),
     'direccion' => $faker->streetAddress
   ];
});
$factory->define(App\CompraVenta::class, function (Faker $faker){
    return [
        'fecha_hora' => $faker->dateTimeThisMonth($max = 'now'),
        'tipo'       => 'co',
        'pagada'     => true,
        'descuento'  => 0,
        'total'      => $faker->randomNumber(3,false),
        'nulo'       => false,
        'fecha_nulo' => null,
        'empleado_id' => 3,
        'caja_id'     => 1
    ];
});