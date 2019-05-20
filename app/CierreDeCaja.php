<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CierreDeCaja extends Model
{
    protected $table = "cierres_cajas";
    protected $fillable=[
        'fecha',
        'efectivo_inicial',
        'compras',
        'pagos_compras',
        'compras_nulas',
        'ventas',
        'pagos_ventas',
        'ventas_nulas',
        'depositos',
        'retiros',
        'efectivo_final',
        'caja_id'
    ];
    protected $primaryKey = 'id';
    public $timestamps=false;

    public function caja(){
        return $this->belongsTo(Caja::class,'caja_id');
    }
}
