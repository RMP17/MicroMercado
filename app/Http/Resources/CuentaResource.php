<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class CuentaResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id_persona' => $this->id_persona,
//            'nombre' => $this->personas->nombre.' '.$this->personas->apellido1.' '.$this->personas->apellido2,
            'nombre' => $this->personas->nombre,
            'usuario' => $this->usuario,
            'contrasenia' => $this->pass_sin_encriptar,
            'nivel_acceso' => $this->nivel_acceso,
            'permisos' => [
                'option1' => strpos($this->permisos, '0')!== false ? true:false,
                'option2' => strpos($this->permisos, '1')!== false ? true:false,
            ],
            'habilitada' => $this->habilitada ? true:false
        ];
    }
}
