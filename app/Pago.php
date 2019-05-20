<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    protected $table = 'pagos';
    protected $fillable = ['cuota','fecha_hora', 'compra_venta_id'];
    protected $primaryKey = 'id';

    public function comprasVentas(){
        return $this->belongsTo(CompraVenta::class,'compra_venta_id','id');
    }
}
