<?php

namespace App\Http\Controllers;

use App\Venta;
use App\Persona;
use Illuminate\Http\Request;

use Carbon\Carbon;
use Validator;

class VentaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $data = json_decode($request->json, true);
        $validator = Validator::make($data, [
            'detalle' => ['required']
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $today = Carbon::now();
        $venta = new Venta();
        $venta->fecha_hora = $today->format('Y-m-d H:i:s');
        if($data['tipo'] == true) {
            $venta->tipo = 'cr';
        } else {
            $venta->tipo = 'co';
        }
        $cliente = Persona::find($data['cliente']['id']);
        if(!is_null($cliente)) {
            $venta->cliente_id = $data['cliente']['id'];
        } else {
            if(!is_null($data['cliente']['id']) && strlen($data['cliente']['ci']) > 0 && strlen($data['cliente']['nombre']) > 0) {
                $cliente = new Persona();
                $cliente->fill($data['cliente']);
                $cliente->save();
                $venta->cliente_id = $cliente->id;
            } else {
                $venta->cliente_id = null;
            }
        }
        $venta->empleado_id = $venta->cliente_id;
        $venta->save();
//        return response()->json($data);
        foreach ($data['detalle'] as $detalle) {
            $venta->productos()->attach($detalle['id_producto'], [
                'precio_unitario' => $detalle['precio_unitario'],
                'cantidad_producto' => $detalle['cantidad_producto']
            ]);
        }
        return response()->json(['status'=> 200],200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    public function showVentas(Request $request)
    {
//        return response()->json($request->all())
        $data = json_decode($request->json, true);
        $validator = Validator::make($data, [
            'fecha' => ['required']
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
        $ventas = Venta::where('fecha_hora',$data['fecha']);
        if(strlen($data['fecha']) > 0) {
            $ventas = $ventas->where('empleado_id');
        }
        $ventas = $ventas->get();
        return response()->json(['data'=>$ventas, 'status'=>200],200);
    }
	public function showVentasCredito()
    {
        $ventas = Venta::where('tipo','cr')->get();
        return response()->json(['data'=>$ventas, 'status'=>200],200);
    }
}
