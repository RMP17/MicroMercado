<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    protected $table = "personas";
    protected $fillable=['ci', 'nombre', 'telefono', 'direccion', 'cliente', 'proveedor', 'empleado'];
    protected $primaryKey = 'id';
    public $timestamps=false;
    public function productos(){
        return $this->belongsToMany(Producto::class,'productos_proveedores','id_producto','id_proveedor')
            ->withPivot('costo_unitario');
    }
    public function ventas(){
        return $this->hasMany(Venta::class, 'empleado_id', 'id');
    }
    public function cuentas(){
        return $this->hasOne( Cuenta::class,'id_persona','id');
    }
    public function compras(){
        return $this->hasMany( Compra::class,'proveedor_id','id');
    }
}
