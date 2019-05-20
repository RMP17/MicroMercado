<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;
use App\Persona;

class MovementResource extends Resource
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
            'empleado' => Persona::find($this->id_persona)->nombre,
            'monto' => $this->pivot->monto,
            'fecha_hora' => $this->pivot->fecha_hora,
            'tipo' => $this->pivot->tipo,
        ];
    }
}