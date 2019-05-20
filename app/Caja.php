<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Caja extends Model
{
    protected $table = "cajas";
    protected $fillable=['descripcion', 'ip', 'efectivo'];
    protected $primaryKey = 'id';
    public $timestamps=false;

    public function cuentas(){
        return $this->belongsToMany(Cuenta::class,'movimientos','id_caja','id_cuenta')
            ->withPivot( 'monto', 'fecha_hora', 'tipo');
    }
    public function comprasVentas(){
        return $this->hasMany(CompraVenta::class,'caja_id','id');
    }
    public function cierresDeCajas(){
        return $this->hasMany(CierreDeCaja::class,'caja_id','id');
    }
}
