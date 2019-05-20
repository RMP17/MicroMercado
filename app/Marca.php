<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Marca extends Model
{
    protected $table = "marcas";
    protected $fillable=['nombre'];
    protected $primaryKey = 'id';
    public $timestamps=false;

    public function productos(){
        return $this->belongsToMany(Producto::class,'productos_marcas','id_producto','id_marca');
    }
}
