<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;
use App\Persona;

class SessionResource extends Resource
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
            'inicio_fecha_hora' => $this->inicio_fecha_hora,
            'fin_fecha_hora' => $this->fin_fecha_hora,
            'usuario' => $this->cuenta_id ? Persona::find($this->cuenta_id)->nombre: '',
        ];
    }
}
