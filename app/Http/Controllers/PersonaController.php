<?php

namespace App\Http\Controllers;

use App\CompraVenta;
use App\Http\Resources\PersonasCollection;
use App\Http\Resources\PersonaResource;
use App\Http\Resources\PersonaNameResource;
use Illuminate\Http\Request;
use App\Persona;
use Validator;

class PersonaController extends Controller
{
    public function __construct()
    {
        $this->middleware('is_admin', ['only' => ['update']]);
    }
    public function index()
    {
        return (new PersonasCollection(Persona::all()))->additional([
            'status' => 200,
        ]);
    }
    public function store(Request $request)
    {
        $data = json_decode($request->json, true);

        $validator = Validator::make($data, [
            'nombre' => ['string', 'required']
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
        $persona = new Persona();
        $persona->fill($data);
        if ($data['rol']=='r')
            $persona->proveedor=true;
        else if ($data['rol']=='c')
            $persona->cliente=true;
        else if ($data['rol']=='e')
            $persona->empleado=true;
        $persona->save();
        return response()->json($persona,200);
    }
    public function update(Request $request, $id)
    {
        if($id == 2) {
            return response()->json(['errors' => 'Los datos de esta persona no pueden ser cambiados'], 400);
        }
        $data = json_decode($request->json, true);
        $validator = Validator::make($data, [
            'nombre' => ['string', 'required'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $_persona = Persona::find($id);
        $_persona->fill($data);
        $_persona->save();
        return response()->json(null, 200);
    }

    public function getProveedores()
    {
        return (new PersonasCollection(Persona::where('proveedor',1)->get()))->additional([
            'status' => 200,
        ]);
    }
    public function getEmpleados()
    {
        return (PersonaNameResource::collection(Persona::where('empleado',1)->get()))->additional([
            'status' => 200,
        ]);
    }
    public function getProveedoresOnlyName()
    {
        return (PersonaNameResource::collection(Persona::where('proveedor',1)->get()))->additional([
            'status' => 200,
        ]);
    }
    public function searchPersonaCi($ci=null)
    {
        if (isset($ci) && strlen($ci) > 0 ){
            $persona = Persona::where('ci',$ci)->first();
            if(!is_null($persona)) {
                return (new PersonaNameResource($persona))->additional([
                    'status' => 200,
                ]);
            }
        }
        return response()->json(null, 200);
    }
    public function contratar($id=null)
    {
        if (isset($id) && strlen($id) > 0 ){
            $persona = Persona::find($id);
            if(!is_null($persona)) {
                $persona->empleado=1;
                $persona->save();
                return (new PersonaNameResource($persona))->additional([
                    'status' => 200,
                ]);
            }
        }
        return response()->json(null, 400);
    }
    public function showSuggestions(Request $request)
    {
        $data = json_decode($request->json, true);
        if (isset($data['q']) && strlen($data['q']) > 0 ){
            $persona = Persona::where('nombre', 'like', '%'.$data['q'].'%')->take(9)->get();
            return (PersonaResource::collection($persona))->additional([
                'status' => 200,
            ]);
        }
        return response()->json(['status'=>200], 200);
    }
    public function getAllEmpleados()
    {
        return (PersonaNameResource::collection(Persona::where('empleado',1)->get()))->additional([
            'status' => 200,
        ]);
    }
    public function getFrequentClients($initialDate, $endDate) {
        $initialDate = $initialDate.' 00:00:00';
        $endDate = $endDate.' 23:59:59';
        $ventas = CompraVenta::join('ventas','ventas.id_compra_venta','compras_ventas.id')
            ->whereBetween('compras_ventas.fecha_hora',[$initialDate, $endDate])
            ->where('compras_ventas.pagada', 1)->get();
        $montoTotalPorCliente = [];
        foreach ($ventas as $venta) {
            if(!isset($montoTotalPorCliente[$venta->cliente_id])) {
                $montoTotalPorCliente[$venta->cliente_id] = 0;
            }
            $montoTotalPorCliente[$venta->cliente_id] =$montoTotalPorCliente[$venta->cliente_id] + $venta->total - $venta->descuento;
        }
        $frequentClients = [];
        foreach ($montoTotalPorCliente as  $key => $value) {
            $persona = Persona::find($key);
            $persona->total_compras = $value;
            array_push($frequentClients, $persona);
        }
        return response()->json($frequentClients);
    }
}
