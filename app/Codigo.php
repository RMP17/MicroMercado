<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Codigo extends Model
{
    protected $table = "codigos";
    protected $fillable=['codigo','producto_id'];
    protected $primaryKey = 'id';
    public $timestamps=false;

    public function productos(){
        return $this->belongsTo(Producto::class,'producto_id');
    }
}
