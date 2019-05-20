<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class MovementsResource extends Resource
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
            'descripcion' => $this->descripcion,
//            'empleado' => Persona::find($this->cuentas()->id_persona)->nombre,
            'movimientos' => MovementResource::collection($this->cuentas),
//            'fecha_hora' => $this->cuentas()->pivot->fecha_hora,
//            'tipo' => $this->cuentas()->pivot->tipo,
        ];
    }
}
