<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class FacturaResource extends Resource
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
            'nro_factura' => $this->nro_factura,
            'nro_autorizacion' => $this->nro_autorizacion,
            'nulo' => $this->nulo,
            'codigo_control' => $this->codigo_control,
        ];
    }
}
