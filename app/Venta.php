<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    protected $table = "ventas";
    protected $fillable=['venta_menor', 'cliente_id'];
    protected $primaryKey = 'id_compra_venta';
    public $timestamps=false;

    public function comprasVentas(){
        return $this->hasOne(CompraVenta::class, 'id', 'id_compra_venta');
    }
    public function cliente(){
        return $this->belongsTo(Persona::class,'cliente_id');
    }
    public function FacturaVentaMenor(){
        return $this->belongsTo(FacturaVentaMenor::class,'id_compra_venta');
    }
}