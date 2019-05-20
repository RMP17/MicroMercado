<?php

namespace App\Http\Controllers;

use App\Configuracion;
use App\FacturaVentaMenor;
use App\Http\Resources\FacturaVentaMenorResource;
use App\Http\Resources\ListaVentaResource;
use App\Http\Resources\VentaExportResource;
use App\Http\Resources\VentaResource;
use App\Http\Resources\FacturaResource;
use App\Pago;
use function GuzzleHttp\Promise\all;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\CompraVenta;
use App\Http\Resources\CompraResource;
use App\Http\Resources\ProductoSimpleResource;
use App\Producto;
use Validator;
use Carbon\Carbon;
use App\Persona;
use App\Compra;
use App\Venta;
use App\Caja;
use App\Factura;
use App\Classes\ControlCode\ControlCode;

class CompraVentaController extends Controller
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
        DB::beginTransaction();
        try {
            $data = json_decode($request->json, true);
            $data['cliente_id'] = $data['cliente_id'] ? $data['cliente_id'] : 2;
            $data['proveedor_id'] = $data['proveedor_id'] ? $data['proveedor_id'] : 2;
            $cliente = Persona::find(2);
            if (is_null($cliente)) {
                $cliente = new Persona();
                $cliente->id = 2;
                $cliente->ci = 0;
                $cliente->nombre = 'S/N';
                $cliente->telefono = '';
                $cliente->direccion = '';
                $cliente->proveedor = true;
                $cliente->cliente = true;
                $cliente->save();
            }
            // Efectivo es el monto que pago el cliente
            // En el caso que fuera venta al credito el efecto sera igual al monto a pagar
            // Efectivo en ventas al credito solo es para mostrar en la factura no es el monto que se pago en el momento de la venta
            $data['efectivo'] = $data['efectivo'] ? $data['efectivo'] : $data['total'] - $data['descuento'];
            if ($data['efectivo'] < $data['total'] - $data['descuento']) {
                return response()->json(['errors' => 'Efectivo debe ser mayor o igual al monto a pagar'], 400);
            }
            if ($data['f'] == true) {
                $configuracion = Configuracion::first();
                if (is_null($configuracion)) {
                    return response()->json('Para realizar ventas con factura es necesario llenar los campos en configuración', 400);
                }
                $today = Carbon::now();
                $today = $today->format('Y-m-d');
                $today = Carbon::parse($today);
                $fecha_limite_emision = Carbon::parse($configuracion->fecha_limite_emision . ' 23:59:59');
                if (($today > $fecha_limite_emision) && !$data['venta_menor']) {
                    return response()->json('Actualice la fecha de limite de emisión', 400);
                }

            }
            $data['descuento'] = $data['descuento'] ? $data['descuento'] : 0;
            $validator = Validator::make($data, [
                'detalle' => ['required'],
                'actividad' => ['required'],
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }

            foreach ($data['detalle'] as $detalle) {
                foreach ($data['detalle'] as $detalle) {
                    $validator = Validator::make($detalle, [
                        'id_producto' => ['required'],
                        'precio_unitario' => ['numeric', 'min:1'],
                        'cantidad_producto' => ['numeric', 'min:1']
                    ]);
                    if ($validator->fails()) {
                        return response()->json(['errors' => $validator->errors()], 400);
                    }
                }
                $user = request()->user();
                $caja = Caja::lockForUpdate()->where('ip', request()->ip())->first();
                if (is_null($caja)) {
                    return response()->json('Este equipo no está asignado a ninguna caja', 400);
                }
                if ($data['total'] < $data['descuento']) {
                    return response()->json('Descuento debe ser menor al total', 400);
                }
                if ($data['actividad'] == 'c') {
                    if ($caja->efectivo - $data['total'] < 0 && $data['tipo'] == false) {
                        return response()->json('Efectivo insuficiente', 400);
                    }
                }
                /*$errorCantidad = false;
                $productoInsuficiente = '';*/
                $today = Carbon::now();
                $compraVenta = new CompraVenta();
                $compraVenta->fecha_hora = $today->format('Y-m-d H:i:s');
                if ($data['tipo'] == true) {
                    $compraVenta->tipo = 'cr';
                    $compraVenta->pagada = false;
                    $data['efectivo'] = $data['total'] - $data['descuento'];
                } else {
                    $compraVenta->tipo = 'co';
                    $compraVenta->pagada = true;
                }
                $compraVenta->descuento = $data['descuento'] ? $data['descuento'] : 0;
                $compraVenta->total = $data['total'] ? $data['total'] : 0;
                $compraVenta->efectivo = $data['efectivo'];
                $compraVenta->empleado_id = $user->id_persona;
                $compraVenta->caja_id = $caja->id;
                $compraVenta->save();
                // Crea los detalles
                if ($data['actividad'] == 'c') { // verifica si es compra o venta
                    if (!is_null($data['proveedor_id'])) {
                        $proveedor = Persona::find($data['proveedor_id']);
                        if (!is_null($proveedor)) {
                            $compra = new Compra();
                            $compra->proveedor_id = $data['proveedor_id'];
                            $compraVenta->compras()->save($compra);
                        }
                    } else {
                        $compra = new Compra();
                        $compraVenta->compras()->save($compra);
                    }
                } else if ($data['actividad'] == 'v') {
                    $venta = new Venta();
                    $venta->venta_menor = $data['venta_menor'];
                    $venta->cliente_id = $data['cliente_id'];
                    $compraVenta->ventas()->save($venta);
                }
                $preciototal = 0;
                foreach ($data['detalle'] as $detalle) {
                    $producto = Producto::lockForUpdate()->find($detalle['id_producto']);
                    $existProveedor = $producto->proveedores()->wherePivot('id_proveedor', $data['proveedor_id'])->first();
                    if (is_null($existProveedor) && $data['proveedor_id']) {
                        $producto->proveedores()->attach($data['proveedor_id']);
                    }
                    if ($data['actividad'] == 'v') {
                        if ($producto->stock - $detalle['cantidad_producto'] < 0) {
                            $productoInsuficiente = $producto->descripcion;
                            DB::rollback();
                            return response()->json(['errors' => 'Stock insuficiente del producto ' . $productoInsuficiente], 400);
                        }
                        $producto->stock -= $detalle['cantidad_producto'];
                        $preciototal = $preciototal + $detalle['cantidad_producto'] * $detalle['precio_unitario'];
                    } else {
                        $producto->precio_compra_unidad = $detalle['precio_unitario'];
                        $preciototal = $preciototal + $detalle['cantidad_producto'] * $detalle['precio_unitario'];
                        if ($data['destino'] == true) {
                            $producto->stock += $detalle['cantidad_producto'];

                        } else {
                            $producto->cantidad_almacen += $detalle['cantidad_producto'];
                        }
                    }
                    $producto->save();
                    $compraVenta->productos()->attach($detalle['id_producto'], [
                        'precio_unitario' => $detalle['precio_unitario'],
                        'cantidad_producto' => $detalle['cantidad_producto']
                    ]);
                }
                if ($data['tipo'] != true) { // true = credito ; false = contado
                    if ($data['actividad'] == 'v') {
                        $caja->efectivo = round($caja->efectivo + $preciototal - $compraVenta->descuento, 2);
                    } else if ($data['actividad'] == 'c') {
                        $caja->efectivo = round($caja->efectivo - $preciototal + $compraVenta->descuento, 2);
                    }
                    $caja->save();
                }
                if (!$data['venta_menor']) {
                    if ($data['actividad'] == 'v' && $data['f'] == true) {
                        $configuraacion = Configuracion::lockForUpdate()->first();
                        $cliente = Persona::find($data['cliente_id']);
                        $codigo_control = $this->generateControlCode($configuraacion->numero_factura,
                            $cliente->ci, $today->format('Y/m/d'), $preciototal);
                        $factura = new Factura();
                        $factura->nro_factura = $configuraacion->numero_factura;
                        $factura->nro_autorizacion = $configuraacion->autorizacion;
                        $factura->codigo_control = $codigo_control;
                        $compraVenta->factura()->save($factura);

                        $configuraacion->numero_factura += 1;
                        $configuraacion->save();
                    }

                }
                // Verifica si total introducido es igual al calculado
                // si es igual no hace nada
                // si no es igual se asigna el valor calculado a la base de datos
                if (!($compraVenta->total == round($preciototal, 2))) {
                    $compraVenta->total = round($preciototal, 2);
                    $compraVenta->save();
                }
                DB::commit();
//            $compraVenta = CompraVenta::find($compraVenta->id);
                $compraVenta->factura;
                $compraVenta->productos;
                return new VentaResource($compraVenta);
            }
        }
        catch
            (\Exception $e) {
                DB::rollBack();
                return response()->json(['status' => false, 'message' => $e->getMessage()], 400);
            }
        /*DB::transaction(function() use($data, $user ,$caja, &$errorCantidad, &$productoInsuficiente, $compraVenta) {
            // Crea nueva compra o venta
            $today = Carbon::now();
            $compraVenta = new CompraVenta();
            $compraVenta->fecha_hora = $today->format('Y-m-d H:i:s');
            if($data['tipo'] == true) {
                $compraVenta->tipo = 'cr';
            } else {
                $compraVenta->tipo = 'co';
            }
            $compraVenta->descuento = $data['descuento'] ? $data['descuento']:0;
            $compraVenta->total = $data['total'] ? $data['total']:0;
            $compraVenta->empleado_id = $user->id_persona ;
            $compraVenta->caja_id = $caja->id;
            $compraVenta->save();
            // Crea los detalles
            if ($data['actividad'] == 'c') { // verifica si es compra o venta
                if(!is_null($data['proveedor_id'])) {
                    $proveedor = Persona::find($data['proveedor_id']);
                    if(!is_null($proveedor)) {
                        $compra = new Compra();
                        $compra->proveedor_id = $data['proveedor_id'];
                        $compraVenta->compras()->save($compra);
                    }
                } else {
                    $compra = new Compra();
                    $compraVenta->compras()->save($compra);
                }
            } else if ($data['actividad'] == 'v') {
                if (!is_null($data['cliente_id'])) {
                    $cliente = Persona::find($data['cliente_id']);
                    if(!is_null($cliente)) {
                        $venta = new Venta();
                        $venta->venta_menor = $data['venta_menor'];
                        $venta->cliente_id = $data['cliente_id'];
                        $compraVenta->ventas()->save($venta);
                    }
                } else {
                    $venta = new Venta();
                    $venta->venta_menor = $data['venta_menor'];
                    $compraVenta->ventas()->save($venta);
                }
            }
            $preciototal = 0;
            foreach ($data['detalle'] as $detalle) {
                $producto = Producto::find($detalle['id_producto']);
                $existProveedor = $producto->proveedores()->wherePivot('id_proveedor',$data['proveedor_id'])->first();
                if(is_null($existProveedor) && $data['proveedor_id']) {
                    $producto->proveedores()->attach($data['proveedor_id']);
                }
                if($data['actividad'] == 'v') {
                    if($producto->stock - $detalle['cantidad_producto'] < 0) {
                        $errorCantidad = true;
                        $productoInsuficiente = $producto->descripcion;
                        return response()->json(['errors'=> 'Stock insuficiente del producto '.$productoInsuficiente],400);
                    }
                    $producto->stock -=$detalle['cantidad_producto'];
                    $preciototal = $preciototal + $detalle['cantidad_producto'] * $detalle['precio_unitario'];
                } else {
                    $producto->precio_compra_unidad =$detalle['precio_unitario'];
                    $preciototal = $preciototal + $detalle['cantidad_producto'] * $detalle['precio_unitario'];
                    if($data['destino'] == true) {
                        $producto->stock +=$detalle['cantidad_producto'];

                    } else {
                        $producto->cantidad_almacen +=$detalle['cantidad_producto'];
                    }
                }
                $producto->save();
                $compraVenta->productos()->attach($detalle['id_producto'], [
                    'precio_unitario' => $detalle['precio_unitario'],
                    'cantidad_producto' => $detalle['cantidad_producto']
                ]);
            }
            if($data['tipo'] != true) { // true = credito ; false = contado
                if($data['actividad'] == 'v') {
                    $caja->efectivo = round($caja->efectivo + $preciototal,2);
                } else if($data['actividad'] == 'c') {
                    $caja->efectivo = round($caja->efectivo - $preciototal,2);
                }
                $caja->save();
            }
            if(!$data['venta_menor']) {
                if($data['actividad'] == 'v' && $data['f'] == true) {
                    $configuraacion = Configuracion::first();
                    $cliente = Persona::find($data['cliente_id']);
                    $codigo_control= $this->generateControlCode($configuraacion->numero_factura,
                        $cliente->ci, $today->format('Y/m/d'), $preciototal); // corregir cuando cliente no es introducido
                    $factura = new Factura();
                    $factura->nro_factura = $configuraacion->numero_factura;
                    $factura->nro_autorizacion = $configuraacion->autorizacion;
                    $factura->codigo_control = $codigo_control;
                    $compraVenta->factura()->save($factura);

                    $configuraacion->numero_factura += 1;
                    $configuraacion->save();
                }

            }
            // Verifica si total introducido es igual al calculado
            // si es igual no hace nada
            // si no es igual se asigna el valor calculado a la base de datos
            if (!($compraVenta->total == $preciototal)) {
                $compraVenta->total = $preciototal;
                $compraVenta->save();
            }
        });*/
        /*if ($errorCantidad) {
            return response()->json(['errors'=> 'Stock insuficiente del producto '.$productoInsuficiente],400);
        }*/
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
    public function showCompra($date1, $date2, $empleado_id = null)
    {
        $dates = ['date_start' => $date1, 'date_end'=> $date2];
        $validator = Validator::make($dates, [
            'date_start' => ['required', 'date_format:Y-m-d'],
            'date_end' => ['required', 'date_format:Y-m-d'],
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
        $d1 = Carbon::parse($date1);
        $d2 = Carbon::parse($date2);
        if ($d1 > $d2) {
            return response()->json(['errors' => 'Fecha de inicio debe ser menor o igual a la fecha final'],400);
        }
        $compras = CompraVenta::join('compras','compras.id_compra_venta', '=', 'compras_ventas.id')
            ->whereBetween('fecha_hora', [$date1.' 00:00:00',$date2.' 23:59:59']);
        if(!is_null($empleado_id)) {
            $compras->where('empleado_id',$empleado_id);
        }
        $compras = $compras->orderBy('fecha_hora', 'desc')->get();
//                return response()->json();
//        $compras = CompraVenta::join('compras','compras.id_compra_venta', '=', 'compras_ventas.id')->get();
        foreach ($compras as $compra) {
            $compra->productos;
            $compra->empleados;
            $compra->pagos;
            $_compra = Compra::find($compra->id);
            if (!is_null($_compra)) {
                $compra->proveedor = $_compra->proveedores;
            }
        }
//        return response()->json($compras);
        return (CompraResource::collection($compras))->additional([
            'status' => 200,
        ]);
    }
    public function showCreditosCompras()
    {
        $creditos = CompraVenta::join('compras','compras.id_compra_venta','compras_ventas.id')
                ->where('tipo','cr')
                ->where('pagada',0);
        $creditos = $creditos->orderBy('fecha_hora', 'asc')->get();
//                return response()->json();
//        $compras = CompraVenta::join('compras','compras.id_compra_venta', '=', 'compras_ventas.id')->get();
        foreach ($creditos as $credito) {
            $credito->productos;
            $credito->empleados;
            $credito->pagos;
            $_compra = Compra::find($credito->id);
            if (!is_null($_compra)) {
                $credito->proveedor = $_compra->proveedores;
            }
        }
//        return response()->json($compras);
        return (CompraResource::collection($creditos))->additional([
            'status' => 200,
        ]);
    }
    public function showCreditosVentas()
    {
        $creditos = CompraVenta::join('ventas','ventas.id_compra_venta','compras_ventas.id')
                ->where('tipo','cr')
                ->where('pagada',0);
        $creditos = $creditos->orderBy('fecha_hora', 'asc')->get();
//                return response()->json();
//        $compras = CompraVenta::join('compras','compras.id_compra_venta', '=', 'compras_ventas.id')->get();
        foreach ($creditos as $credito) {
            $credito->productos;
            $credito->empleados;
            $credito->pagos;
            $_venta = Venta::find($credito->id);
            if (!is_null($_venta)) {
                $credito->cliente = $_venta->cliente;
            }
        }
//        return response()->json($compras);
        return (ListaVentaResource::collection($creditos))->additional([
            'status' => 200,
        ]);
    }
    public function realizarPago(Request $request)
    {
            $data = json_decode($request->json, true);
            $validator = Validator::make($data, [
                'cuota' => ['numeric','min:1','required'],
                'proveedor_id' => ['required']
            ]);
            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }
            $caja = Caja::where('ip',request()->ip())->first();
            if ($caja->efectivo - $data['cuota'] < 0) {
                return response()->json('Insuficiente efectivo en caja', 400);
            }
        DB::transaction(function() use(&$data, $caja) {
            $today = Carbon::now();
            $id = null;
            while ($data['cuota'] > 0 ){
                $compras = CompraVenta::join('compras','compras.id_compra_venta','compras_ventas.id')
                    ->where('proveedor_id',$data['proveedor_id'])
                    ->where('compras_ventas.tipo','cr')
                    ->where('compras_ventas.pagada',0)
                    ->orderBy('compras_ventas.id','asc')->first();
                if (is_null($compras) || $id == $compras->id) {
                    break;
                }
                $montoTotal = Pago::where('compra_venta_id', $compras->id)->get()->sum('cuota');
                $totalPagar = $compras->total - ($montoTotal + $compras->descuento + $data['cuota']);
                $pago = new Pago();
                if($totalPagar > 0) {
                    $pago->cuota = $data['cuota'];
                    $caja->efectivo -= $data['cuota'];
                    $data['cuota'] = 0;
                } else {
                    $data['cuota'] = $data['cuota'] + $montoTotal + $compras->descuento - $compras->total;
                    $pago->cuota = $compras->total - $montoTotal - $compras->descuento;
                    $caja->efectivo = $caja->efectivo-$pago->cuota;
//                    $compras->tipo = 'co';
                    $compras->pagada = true;
                }
                $pago->fecha_hora=$today->format('Y-m-d H:i:s');
                $compras->save();
                $compras->pagos()->save($pago);
                $caja->save();
                $id = $compras->id;
            }
        });
            return response()->json(['cambio' => $data['cuota']]);
    }
    public function registerPagoDeudor(Request $request)
    {
            $data = json_decode($request->json, true);
            $validator = Validator::make($data, [
                'cuota' => ['numeric','min:1','required'],
                'deudor_id' => ['required']
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }
            $ip = request()->ip();
        DB::transaction(function() use(&$data, $ip) {
            $caja = Caja::where('ip',$ip )->first();
            $today = Carbon::now();
            $id = null;
            while ($data['cuota'] > 0 ){
                $ventas = CompraVenta::join('ventas','ventas.id_compra_venta','compras_ventas.id')
                    ->where('cliente_id',$data['deudor_id'])
                    ->where('compras_ventas.tipo','cr')
                    ->where('compras_ventas.pagada',0)
                    ->orderBy('compras_ventas.id','asc')->first();
                if (is_null($ventas) || $id == $ventas->id) {
                    break;
                }
                $cuotasTotal = Pago::where('compra_venta_id', $ventas->id)->get()->sum('cuota');
                $totalPagar = $ventas->total - ($cuotasTotal + $ventas->descuento + $data['cuota']);
                $pago = new Pago();
                if($totalPagar > 0) {
                    $pago->cuota = $data['cuota'];
                    $caja->efectivo += $data['cuota'];
                    $data['cuota'] = 0;
                } else {
                    $data['cuota'] = $data['cuota'] + $cuotasTotal + $ventas->descuento - $ventas->total;
                    $pago->cuota = $ventas->total - $cuotasTotal - $ventas->descuento;
                    $caja->efectivo = $caja->efectivo+$pago->cuota;
//                    $ventas->tipo = 'co';
                    $ventas->pagada = true;
                }
                $pago->fecha_hora=$today->format('Y-m-d H:i:s');
                $ventas->save();
                $ventas->pagos()->save($pago);
                $caja->save();
            }
        });
            return response()->json(['cambio' => $data['cuota']]);
    }
    /*
     * @param Number $numeroFactura Numero de factura
     * @param Number $identificación Número de Identificación Tributaria o Carnet de Identidad
     * @param Date $fechatransaccion Fecha de transaccion de la forma AAAAMMDD
     * @param Date $monto Monto de la transacción
     */
    public function generateControlCode($numeroFactura, $identificación, $fechatransaccion, $monto)
    {
        $configuracion = Configuracion::first();
        $controlCode = new ControlCode();
        //genera codigo de control
        $code = $controlCode->generate(
            $configuracion->autorizacion,                               //Numero de autorizacion
            $numeroFactura,                                             //Numero de factura
            $identificación,                                            //Número de Identificación Tributaria o Carnet de Identidad
            str_replace('/', '', $fechatransaccion),     //fecha de transaccion de la forma AAAAMMDD
            $monto,                                                     //Monto de la transacción
            $configuracion->dosificacion                                //Llave de dosificación
        );
        return $code;
    }
    public function showVentas($date1, $date2, $empleado_id = null) {
        $dates = ['date_start' => $date1, 'date_end'=> $date2];
        $validator = Validator::make($dates, [
            'date_start' => ['required', 'date_format:Y-m-d'],
            'date_end' => ['required', 'date_format:Y-m-d'],
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
        $d1 = Carbon::parse($date1);
        $d2 = Carbon::parse($date2);
        if ($d1 > $d2) {
            return response()->json(['errors' => 'Fecha de inicio debe ser menor o igual a la fecha final'],400);
        }
        $ventas = CompraVenta::join('ventas','ventas.id_compra_venta', '=', 'compras_ventas.id')
            ->whereBetween('fecha_hora', [$date1.' 00:00:00',$date2.' 23:59:59']);
        /*if(!is_null($empleado_id)) {
            $ventas->where('empleado_id',$empleado_id);
        }*/
        $ventas = $ventas->orderBy('fecha_hora', 'desc')->get();

        foreach ($ventas as $venta) {
            $venta->productos;
            $venta->empleados;
            $venta->pagos;
            $venta->factura;
//            $_venta = Venta::find($venta->id);
            /*if (!is_null($_venta)) {
                $venta->cliente = $_venta->cliente;
            }*/
        }
        return (ListaVentaResource::collection($ventas))->additional([
            'status' => 200,
        ]);
    }
    public function cancelSale($idVenta) {
        try {
            $venta = Venta::find($idVenta);
            if (!is_null($venta->factura_venta_menor_id)) {
                    return response()->json(['errors' => 'Debe anular la factura de ventas menores antes de anular esta venta'],400);
            }
            DB::beginTransaction();
            $today = Carbon::now();
            $compraVenta = CompraVenta::findOrfail($idVenta);
            $compraVenta->nulo = true;
            $compraVenta->fecha_nulo = $today->format('Y-m-d');
            $compraVenta->save();
            if($compraVenta->factura) {
                $compraVenta->factura->nulo = true;
                $compraVenta->factura->save();
            }
            $total = 0;
            foreach ($compraVenta->productos as $producto) {
                $producto->stock += $producto->pivot->cantidad_producto;
                $producto->save();
                $total = $total + $producto->pivot->cantidad_producto * $producto->pivot->precio_unitario;
            }
            $caja = Caja::where('ip',request()->ip())->first();
            if ($caja->efectivo - round($total, 2) < 0) {
                return response()->json(['errors' => 'Efectivo insuficiente en caja']);
            }
            $total -= $compraVenta->descuento;
            $caja->efectivo = $caja->efectivo - round($total, 2);
            $caja->save();
            DB::commit();
            return response()->json([]);
        } catch (ModelNotFoundException $e) {
            return response()->json($e, 400);
        }
    }
    public function cancelInvoice($idVenta) {
        try {
            $invoice = Factura::findOrFail($idVenta);
            $invoice->nulo = true;
            $invoice->save();
            return response()->json([]);
        } catch (ModelNotFoundException $e) {
            return response()->json($e, 400);
        }
    }
    public function addInvoice(Request $request) {
        try {
            $data = json_decode($request->json, true);
            $validator = Validator::make($data, [
                'id_compra_venta' => ['required'],
                'nro_factura' => ['required'],
                'nro_autorizacion' => ['required']
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }
            $invoice = Factura::where('nro_factura',$data['nro_factura'])->first();
            if(is_null($invoice)) {
                $compraVenta = CompraVenta::findOrFail($data['id_compra_venta']);
                $invoice = new Factura();
                $invoice->fill($data);
                $compraVenta->factura()->save($invoice);
            } else {
                return response()->json(['errors' => 'La venta ya tiene factura'], 400);
            }
           return new FacturaResource($invoice);
        } catch (ModelNotFoundException $e) {
            return response()->json($e, 400);
        }
    }
    public function showVentaForExport($date1, $date2) {
        $ventasFacturadas = Factura::join('compras_ventas','compras_ventas.id', '=', 'facturas.id_compra_venta')
            ->join('ventas','ventas.id_compra_venta', '=', 'facturas.id_compra_venta')
            ->whereBetween('compras_ventas.fecha_hora', [$date1.' 00:00:00',$date2.' 23:59:59'])
            ->orderBy('compras_ventas.fecha_hora', 'asc')
            ->get();
//        return response()->json($ventasFacturadas);
        $lowerSalesInvoice = FacturaVentaMenor::whereBetween('fecha', [$date1.' 00:00:00',$date2.' 23:59:59'])
            ->orderBy('fecha', 'asc')
            ->get();
        foreach ($lowerSalesInvoice as $value) {
            $value->cliente = [
                'ci' => 0,
                'nombre' => 'VENTAS MENORES',
            ];
        }
        return (VentaExportResource::collection($ventasFacturadas))->additional([
            'lowerSalesInvoice' => $lowerSalesInvoice,
        ]);
    }
    public function showVentasMenoresForExport($date1, $date2) {
        $ventasMenores = CompraVenta::join('ventas','compras_ventas.id', '=', 'ventas.id_compra_venta')
            ->where('ventas.venta_menor',1)
            ->where('compras_ventas.nulo',0)
            ->whereBetween('compras_ventas.fecha_hora', [$date1.' 00:00:00',$date2.' 23:59:59'])
            ->orderBy('compras_ventas.fecha_hora', 'asc')
            ->get();
        $ventasMenoresFormatted = [];
        $ventasMenoresDetalle = [];
        foreach ($ventasMenores as $ventaMenor) {
            $date = Carbon::parse($ventaMenor->fecha_hora);
            $date = $date->format('Y-m-d');
            $ventasMenoresDetalle['fecha']= $date;
            $ventasMenoresDetalle['total'] = $ventaMenor->total;
            $ventasMenoresDetalle['descuento'] = $ventaMenor->descuento;
            foreach ($ventaMenor->productos as $producto) {
                $ventasMenoresDetalle['detalle'][] =  [
                    'descripcion' => $producto->descripcion,
                    'precio_unitario' => $producto->pivot->precio_unitario,
                    'cantidad_producto' => $producto->pivot->cantidad_producto,
                ];
            }
            array_push($ventasMenoresFormatted, $ventasMenoresDetalle);
            $ventasMenoresDetalle = [];
        }
        return response()->json($ventasMenoresFormatted);
    }
    public function generateLowersSalesInvoice(Request $request) {
        try {
            $date = json_decode($request->json, true);
            DB::beginTransaction();
            $ventasMenores = CompraVenta::join('ventas','compras_ventas.id', '=', 'ventas.id_compra_venta')
                ->where('ventas.venta_menor',1)
                ->where('ventas.factura_venta_menor_id', null)
                ->where('compras_ventas.nulo',0)
                ->whereBetween('compras_ventas.fecha_hora', [$date.' 00:00:00',$date.' 23:59:59'])
                ->get();
            if(count($ventasMenores) < 1) {
                return response()->json(['errors' => 'Las ventas menores ya están facturadas'], 400);
            }
            $total = 0;
            $descuento = 0;
            foreach ($ventasMenores as $ventaMenor) {
                $total += $ventaMenor->total;
                $descuento += $ventaMenor->descuento;
            }
            $total = round($total,2);
            $dateFormated = Carbon::parse($date);
            $dateFormated = $dateFormated->format('Y/m/d');
            $configuraacion = Configuracion::first();
            $codigo_control= $this->generateControlCode($configuraacion->numero_factura,
                0, $dateFormated, $total - $descuento);

            $facturaVentasMenores = new FacturaVentaMenor();
            $facturaVentasMenores->fecha = $date;
            $facturaVentasMenores->total = $total;
            $facturaVentasMenores->descuento = $descuento;
            $facturaVentasMenores->nro_factura = $configuraacion->numero_factura ;
            $facturaVentasMenores->nro_autorizacion = $configuraacion->autorizacion;
            $facturaVentasMenores->codigo_control = $codigo_control;
            $facturaVentasMenores->save();
            $configuraacion->numero_factura += 1;
            $configuraacion->save();
            CompraVenta::join('ventas','compras_ventas.id', '=', 'ventas.id_compra_venta')
                ->where('ventas.venta_menor',1)
                ->where('ventas.factura_venta_menor_id',null)
                ->where('compras_ventas.nulo',0)
                ->whereBetween('compras_ventas.fecha_hora', [$date.' 00:00:00',$date.' 23:59:59'])
                ->update(['ventas.factura_venta_menor_id'=>$facturaVentasMenores->id]);
            DB::commit();
            return response()->json([]);
        } catch (ModelNotFoundException $e) {
            DB::rollback();
            return response()->json($e, 400);
        }
    }
    public function getLowerSalesInvoice($date1, $date2) {
        $ventasMenores = FacturaVentaMenor::whereBetween('fecha', [$date1.' 00:00:00',$date2.' 23:59:59'])->get();
        return FacturaVentaMenorResource::collection($ventasMenores);
    }
    public function getDetailOfLowerSales($idFactura) {
        $ventas = CompraVenta::join('ventas','compras_ventas.id', '=', 'ventas.id_compra_venta')
            ->where('ventas.factura_venta_menor_id', $idFactura)->orderBy('compras_ventas.fecha_hora', 'asc')->get();
        $detail = [];
        foreach ($ventas as $venta) {
            $_detail =  $venta->productos;
            foreach ($_detail as $value)
            array_push($detail, $value);
        }
        return ProductoSimpleResource::collection(collect($detail));
    }
    public function cancelMinorSalesInvoice($idFactura) {
        try {
            DB::beginTransaction();
            $minorSalesInvoice = FacturaVentaMenor::findOrFail($idFactura);
            $minorSalesInvoice->nulo = true;
            $minorSalesInvoice->save();
            Venta::where('factura_venta_menor_id', $idFactura)
                ->update(['factura_venta_menor_id' => null]);
            DB::commit();
            return response()->json([]);
        } catch (ModelNotFoundException $e) {
            return response()->json($e, 400);
        }
    }
}