<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Cuenta;
use App\Persona;

class CuentasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Persona::create([
            'id' => '1',
            'ci' => '123',
            'nombre' => 'Admin A.',
            'telefono' => '',
            'direccion' => '',
            'cliente' => false,
            'proveedor' => false,
            'empleado' => false,
        ]);
        $user = [
            'id_persona'=> '1',
            'usuario'=> '123',
            'contrasenia'=> Hash::make('123'),
            'pass_sin_encriptar'=> '123',
            'nivel_acceso'=> '0',
            'permisos'=> '01',
            'habilitada' => '1'
        ];
        Cuenta::create($user);
    }
}
