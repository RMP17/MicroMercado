<?php

namespace App\Http\Controllers;

use App\Configuracion;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Classes\ControlCode\ControlCode;
use Validator;

class ConfiguracionController extends Controller
{
    public function __construct()
    {
        $this->middleware('is_admin', ['except' => ['testControlCode', 'index']]);
    }

    public function index(){
        $configuracion = Configuracion::first();
        if(is_null($configuracion)) {
            return response()->json('Debe llenar los campos de configuración', 400);
        }
        return response()->json($configuracion);
    }
    public function store(Request $request){
        /*
         * 'nombre_super_mercado',
         'propietario_a',
         'casa_matriz',
         'telefono', 'nit',
         'numero_factura',
        'autorizacion',
        'dosificacion',
        'fecha_limite_emision',
        'dias_antes_mostrar_vencimiento',
        'stock_min_antes_mostrar'
        */
//        $data = json_decode($request->json, true);
        $data = $request->all();
        $data['dosificacion'] =str_replace(' ','',$data['dosificacion'] );
        $validator = Validator::make($data, [
            'nombre_super_mercado' => ['required'],
            'propietario_a' => ['required'],
            'casa_matriz' =>['required'],
            'telefono' =>['required'],
            'nit' =>['required'],
            'numero_factura' =>['required','numeric'],
            'autorizacion' =>['required','numeric'],
            'dosificacion' =>['required'],
            'fecha_limite_emision' =>['required'],
            'dias_antes_mostrar_vencimiento' =>['required'],
            'stock_min_antes_mostrar' =>['required'],
        ]);
        if ($validator->fails()) {
            return response()->json('Todos los datos son requeridos en formatos correctos', 400);
        }
        $configuracion = Configuracion::first();
        if(is_null($configuracion)) {
            $configuracion = new Configuracion();
            $configuracion->fill($data);
        } else {
            $configuracion->fill($data);
        }
        $configuracion->save();
        return response()->json(null);
    }
    public function testControlCode(Request $request){
        $validator = Validator::make($request->all(), [
            'nro_autorizacion' => ['required'],
            'nro_factura' => ['required'],
            'nit' => ['required'],
            'fecha' => ['required'],
            'monto' => ['required'],
            'llave' => ['required']
        ]);
        $data = $request->all();
        if ($validator->fails()) {
            return response()->json('Todos los datos son requeridos en formatos correctos', 400);
        }
        $date = Carbon::parse($data['fecha']);
        $date = $date->format('Y/m/d');
        $controlCode = new ControlCode();
        $codigo_control = $controlCode->generate(
            $data['nro_autorizacion'],                               //Numero de autorizacion
            $data['nro_factura'],                                    //Numero de factura
            $data['nit'],                                            //Número de Identificación Tributaria o Carnet de Identidad
            str_replace('/','',$date),                //fecha de transaccion de la forma AAAAMMDD sin barras
            $data['monto'],                                          //Monto de la transacción
            str_replace(' /g','',$data['llave'])                                           //Llave de dosificación
        );

        return response()->json(['codigo_control' => $codigo_control,
            'llave' => $data['llave']]);
    }
}
