<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;
use App\Persona;

class VentaExportResource extends Resource
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
            'fecha_hora' => $this->fecha_hora,
            'nro_factura' => $this->nro_factura,
            'nro_autorizacion' => $this->nro_autorizacion,
            'nulo' => $this->nulo,
            'codigo_control' => $this->codigo_control,
            'cliente' => $this->cliente_id ? new PersonaNameResource(Persona::find($this->cliente_id)): '',
        ];
    }
}
