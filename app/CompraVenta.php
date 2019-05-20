<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CompraVenta extends Model
{
    protected $table = "compras_ventas";
    protected $fillable=['fecha_hora', 'tipo', 'pagada', 'descuento','total', 'nulo', 'fecha_nulo','empleado_id',
        'caja_id', 'factura_venta_menor_id'];
    protected $primaryKey = 'id';
    public $timestamps=false;

    public function factura() {
        return $this->hasOne(Factura::class, 'id_compra_venta', 'id');
    }
    public function compras() {
        return $this->hasOne(Compra::class, 'id_compra_venta', 'id');
    }
    public function ventas() {
        return $this->hasOne(Venta::class, 'id_compra_venta', 'id');
    }
    public function productos(){
        return $this->belongsToMany(Producto::class,'detalles', 'id_compra_venta', 'id_producto')
            ->withPivot('precio_unitario', 'cantidad_producto')->withTimestamps();
    }
    public function empleados(){
        return $this->belongsTo (Persona::class,'empleado_id','id');
    }
    public function pagos(){
        return $this->hasMany(Pago::class,'compra_venta_id','id');
    }
    /*public function clientes(){
        return $this->belongsTo (Persona::class,'cliente_id','id');
    }*/
    public function caja(){
        return $this->belongsTo(Caja::class,'id');
    }
}
