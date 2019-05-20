<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FacturaVentaMenor extends Model
{
    protected $table = 'facturas_ventas_menores';
    protected $fillable = [ 'fecha_hora', 'total', 'descuento','nro_factura','nro_autorizacion', 'nulo', 'codigo_control'];
    protected $primaryKey = 'id';
    public $timestamps=false;
    public function Ventas(){
        return $this->hasMany(CompraVenta::class,'factura_venta_menor_id','id');
    }
}
