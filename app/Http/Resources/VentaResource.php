<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;
use App\Persona;

class VentaResource extends Resource
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
            'total' => $this->total,
            'descuento' => $this->descuento,
            'efectivo' => $this->efectivo,
            'tipo' => $this->tipo,
            'fecha_hora' => $this->fecha_hora,
            'empleado' => $this->empleados->nombre,
            'factura' => new FacturaResource($this->factura),
            'cliente' => $this->ventas ? new PersonaNameResource(Persona::find($this->ventas->cliente_id)): '',
            'detalle' => ProductoSimpleResource::collection($this->productos)
        ];

    }
}
