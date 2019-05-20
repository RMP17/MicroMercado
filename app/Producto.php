<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = "productos";
    protected $fillable=['descripcion', 'tipo_unidad', 'stock',	'precio_venta_unidad',
        'precio_compra_unidad', 'fecha_caducidad' , 'fecha_caducidad_almacen', 'cantidad_almacen', 'img'];
    protected $primaryKey = 'id';
    protected $guarded = ['notificar_fecha_caducidad', 'noficar'];
    public $timestamps=false;

    public function compraVentas(){
        return $this->belongsToMany(Venta::class,'detalles', 'id_compra_venta', 'id_producto')
            ->withPivot('precio_unitario', 'cantidad_producto')->withTimestamps();
    }
    public function codigos(){
        return $this->hasMany(Codigo::class,'producto_id','id');
    }
    public function marcas(){
        return $this->belongsToMany(Marca::class,'productos_marcas','id_producto','id_marca');
    }
    public function categorias(){
        return $this->belongsToMany(Categoria::class,'productos_categorias','id_producto','id_categoria');
    }
    public function proveedores(){
        return $this->belongsToMany(Persona::class,'productos_proveedores','id_producto','id_proveedor');
    }
}
