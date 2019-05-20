<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class ProductoSimpleResource extends Resource
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
            'descripcion' => $this->descripcion,
            'tipo_unidad' => $this->tipo_unidad,
            'cantidad' => $this->pivot->cantidad_producto,
            'precio_unitario' => $this->pivot->precio_unitario,
        ];
    }
}
