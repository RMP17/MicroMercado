<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class ProductoResource extends Resource
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
            'stock' => $this->stock,
            'precio_compra_unidad' => $this->precio_compra_unidad,
            'precio_venta_unidad' => $this->precio_venta_unidad,
            'fecha_caducidad' => $this->fecha_caducidad,
            'cantidad_almacen' => $this->cantidad_almacen,
            'fecha_caducidad_almacen' => $this->fecha_caducidad_almacen,
            'notificar' => $this->notificar,
            'notificar_fecha_caducidad' => $this->notificar_fecha_caducidad,
            'img' => $this->img,
//            'codigos' => CodigoResource::collection($this->codigos),
//            'codigos' => CodigoResource::collection($this->whenLoaded('codigos')),
//            'codigos' => $this->codigos ? $this->codigos : ''
        ];
    }
}
