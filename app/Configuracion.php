<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    protected $table = "configuracion";
    protected $fillable=['nombre_super_mercado', 'propietario_a', 'casa_matriz', 'telefono', 'nit', 'numero_factura',
        'autorizacion', 'dosificacion', 'fecha_limite_emision', 'dias_antes_mostrar_vencimiento',
        'stock_min_antes_mostrar'];
    protected $primaryKey = 'id';
    public $timestamps=false;
}
