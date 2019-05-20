<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $table = "categorias";
    protected $fillable=['descripcion'];
    protected $primaryKey = 'id';
    public $timestamps=false;

    public function productos(){
        return $this->belongsToMany(Producto::class,'productos_categoria','id_producto','id_categoria');
    }
}
