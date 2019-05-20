<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sesion extends Model
{
    protected $table = "sesiones";
    protected $fillable=['inicio_fecha_hora', 'fin_fecha_hora'];
    protected $primaryKey = 'id';
    public $timestamps=false;

    public function cuenta(){
        return $this->belongsTo(Cuenta::class,'cuenta_id');
    }
}
