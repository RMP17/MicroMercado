<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Factura extends Model
{
    protected $table = 'facturas';
    protected $fillable = ['nro_factura','nro_autorizacion', 'nulo', 'codigo_control'];
    protected $primaryKey = 'id_compra_venta';
    public function comprasVentas(){
        return $this->hasOne(CompraVenta::class,'id','id_compra_venta');
    }
}
