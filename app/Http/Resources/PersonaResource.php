<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class PersonaResource extends Resource
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
            'id' => $this->id,
            'ci' => $this->ci,
            'nombre' => $this->nombre,
            'telefono' => $this->telefono,
            'direccion' => $this->direccion,
        ];
    }
}
