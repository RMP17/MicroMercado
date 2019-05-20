<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;
use App\Persona;

class ListaVentaResource extends Resource
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
            'venta_menor' => $this->ventas->venta_menor,
            'factura' => new FacturaResource($this->factura),
            'cliente' => new PersonaNameResource(Persona::find($this->ventas->cliente_id)),
            'detalle' => ProductoSimpleResource::collection($this->productos),
            'nulo' => $this->nulo,
            'empleado' => $this->empleados->nombre,
            'pagos' => PagoResource::collection($this->pagos)
        ];
    }
}
