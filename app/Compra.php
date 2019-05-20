<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    protected $table = "compras";
    protected $fillable=['proveedor_id'];
    protected $primaryKey = 'id_compra_venta';
    public $timestamps=false;

    public function comprasVentas(){
        return $this->hasOne(CompraVenta::class, 'id', 'id_compra_venta');
    }
    public function proveedores(){
        return $this->belongsTo(Persona::class,'proveedor_id');
    }
}
