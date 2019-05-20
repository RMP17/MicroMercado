<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class CompraResource extends Resource
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
            'fecha_hora' => $this->fecha_hora,
            'tipo' => $this->tipo,
            'descuento' => $this->descuento,
            'total' => $this->total,
            'empleado' => $this->empleados->nombre,
            'proveedor' => is_null($this->proveedor) ? '':$this->proveedor->nombre,
            'pagos' => PagoResource::collection($this->pagos),
            'detalle' => ProductoSimpleResource::collection($this->productos)
        ];
    }
}
