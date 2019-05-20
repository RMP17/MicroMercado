<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Support\Facades\Hash;

class Cuenta extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = "cuentas";
    protected $fillable=['usuario', 'contrasenia', 'pass_sin_encriptar', 'nivel_acceso', 'permisos', 'habilitada'];
    protected $hidden = [
        'contrasenia', 'remember_token',
    ];
    protected $primaryKey = 'id_persona';
    public $timestamps=false;
    public function sesiones(){
        return $this->hasMany(Sesion::class,'cuenta_id','id_persona');
    }
    public function personas(){
        return $this->hasOne(Persona::class,'id','id_persona');
    }
    public function cajas(){
        return $this->belongsToMany(Caja::class,'movimientos','id_cuenta','id_caja')
            ->withPivot('monto', 'fecha_hora', 'tipo');
    }
    public function getAuthPassword() {
        return $this->contrasenia;
    }
    public function setContraseniaAttribute($value) {
        $this->attributes['contrasenia'] = Hash::make($value);
    }
}