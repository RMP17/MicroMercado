<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class PagoResource extends Resource
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
            'cuota' => $this->cuota,
            'fecha_hora' => $this->fecha_hora,
        ];
    }
}
