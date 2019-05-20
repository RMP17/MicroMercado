<?php

namespace App\Http\Controllers;

use App\Caja;
use App\CierreDeCaja;
use App\CompraVenta;
use App\Pago;
use App\Http\Resources\CajasCollection;
use App\Http\Resources\MovementsResource;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CajaController extends Controller
{
    public function __construct()
    {
        $this->middleware('is_admin', ['only' => ['store', 'update', 'destroy']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return (new CajasCollection(Caja::all()))->additional([
            'status' => 200,
        ]);
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
            'descripcion' => ['string', 'required','unique:cajas'],
            'ip' => ['string', 'required','unique:cajas'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
        $data['ip'] = str_replace(' ', '', $data['ip']);
        $data['efectivo'] = 0;
        $caja = new Caja();
        $caja->fill($data);
        $caja->save();

        return response()->json(null, 200);
//        var_dump(request()->ips());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

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
        if (is_null($id)) {
            return response()->json(['errors' => 'Id inexiste'], 400);
        }
        $data = json_decode($request->json, true);
        $validator = Validator::make($data, [
            'descripcion' => ['string', 'required'],
            'ip' => ['string', 'required'],
            'efectivo' => ['numeric', 'min:0'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
        $caja = Caja::find($id);

        if (!is_null($caja)) {
            $caja->fill($data);
            $caja->save();
        } else {
            return response()->json('No existe la caja', 400);
        }

        return response()->json(null, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $cuenta = Caja::findOrFail($id);
            $cuenta->delete();
            return response()->json(['status' => 200], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json($e, 400);
        }
    }
    public function saveMovement(Request $request)
    {
        $data = json_decode($request->json, true);

        $validator = Validator::make($data, [
            'monto' => ['numeric', 'min:1', 'required'],
            'tipo' => ['required'],
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
        $caja = Caja::where('ip', request()->ip())->first();
        if(!is_null($caja) ) {
            /*
             * tipo
             * r = retiro
             * d = deposito
             * */
            DB::beginTransaction();
            if($data['tipo'] == 'r') {
                if( $caja->efectivo - $data['monto'] >= 0) {
                    $caja->efectivo = round($caja->efectivo - $data['monto'], 2);
                } else {
                    return response()->json('Monto debe ser mayor o igual al efectivo en caja',400);
                }

            } else {
                $caja->efectivo = round($caja->efectivo + $data['monto'],2);
            }
            $caja->save();
            $today = Carbon::now();
            $caja->cuentas()->attach(request()->user()->id_persona, [
                'monto' => $data['monto'],
                'fecha_hora' => $today->format('Y-m-d H:i:s'),
                'tipo' => $data['tipo']
            ]);
            DB::commit();
            return response()->json($caja->efectivo,200);
        } else {
            return response()->json('La caja no esta registrado a esta ip',400);
        }
    }
    public function showEfectivo() {
        $caja = Caja::where('ip',request()->ip())->first();
        if(!is_null($caja)) {
            return response()->json($caja->efectivo);
        } else {
            return response()->json('Este equipo no está asignado a ninguna caja',400);
        }
    }
    public function showMovements($date1,$date2) {

        $date1 = $date1.' 00:00:00';
        $date2 = $date2.' 23:59:59';
        $movimientos = Caja::all();
        foreach ($movimientos as $movimiento) {
            $movimiento->cuentas = $movimiento->cuentas()
                ->wherePivot('fecha_hora','>=',$date1)
                ->wherePivot('fecha_hora','<=',$date2)
                ->orderBy('fecha_hora', 'desc')->get();
        }
//        return response()->json($movimientos);
        return MovementsResource::collection($movimientos);
    }
    public function cashClosure($date) { // borrar si no se usa 26/6/2018
//        $date = Carbon::parse($date);
        $cajas = Caja::all();
        $compras = CompraVenta::join('compras','compras.id_compra_venta', '=', 'compras_ventas.id')
            ->where('nulo',0)
            ->whereBetween('fecha_hora', [$date . ' 00:00:00', $date . ' 23:59:59']);
        $ventas = CompraVenta::join('ventas','ventas.id_compra_venta', '=', 'compras_ventas.id')
            ->where('nulo',0)
            ->whereBetween('fecha_hora', [$date . ' 00:00:00', $date . ' 23:59:59']);
//        $_totalCompras = $_totalCreditos = $_totalDepositos = $_totalRetiros = 0;
        foreach ($cajas as $caja) {
            $compras_copy_1 = clone $compras;
            $compras_copy_2 = clone $compras;
            $compras_copy_3 = clone $compras;
            $ventas_copy_1 = clone $ventas;
            $ventas_copy_2 = clone $ventas;
            $ventas_copy_3 = clone $ventas;
            $caja->totalCompras = $compras_copy_1->where('caja_id',$caja->id)
                ->where('tipo','co')
                ->get()->sum('total');
            $pagos = 0;
            $compras_copy_2 = $compras_copy_2->where('caja_id',$caja->id)
                ->where('tipo','co')
                ->get();
            foreach ($compras_copy_2 as $value) {
                $pagos += $value->pagos->sum('cuota');
            }
            $caja->totalCompras -= $pagos;
            /*$caja->totalCreditosCompras = $compras_copy_2->where('caja_id',$caja->id)
                ->where('tipo','cr')
                ->get()->sum('total');*/
            $compras_copy_3 = $compras_copy_3->where('caja_id',$caja->id)
                ->where('tipo','cr')
                ->get();
            $total = 0;
            foreach ($compras_copy_3 as $compra) {
                $total += $compra->pagos()
                    ->whereBetween('pagos.fecha_hora', [$date . ' 00:00:00', $date . ' 23:59:59'])
                    ->get()->sum('cuota');
            }
            $caja->totalPagosCompras = $total;

            $caja->totalVentas = $ventas_copy_1->where('caja_id',$caja->id)
                ->where('tipo','co')
                ->get()->sum('total');
            /*$caja->totalCreditosVentas = $ventas_copy_2->where('caja_id',$caja->id)
                ->where('tipo','cr')
                ->get()->sum('total');*/
            $ventas_copy_3 = $ventas_copy_3->where('caja_id',$caja->id)
                ->where('tipo','cr')
                ->get();
            $total = 0;
            foreach ($ventas_copy_3 as $venta) {
                $total += $venta->pagos->sum('cuota');
            }
            $caja->totalPagosVentas = $total;

            $_caja = $caja->cuentas()
                ->wherePivot('fecha_hora','>=',$date . ' 00:00:00')
                ->wherePivot('fecha_hora','<=',$date . ' 23:59:59');
            $caja_copy_1 = clone $_caja;
            $caja_copy_2 = clone $_caja;

            $caja->totalDepositos = $caja_copy_1->wherePivot('tipo','d')->get()->sum('pivot.monto');
            $caja->totalRetiros = $caja_copy_2->wherePivot('tipo','r')->get()->sum('pivot.monto');
        }
        $compras = CompraVenta::join('compras','compras.id_compra_venta', '=', 'compras_ventas.id')
            ->where('nulo',0)
            ->where('fecha_hora','>',$date . ' 23:59:59');
        $ventas = CompraVenta::join('ventas','ventas.id_compra_venta', '=', 'compras_ventas.id')
            ->where('nulo',0)
            ->where('fecha_hora','>',$date . ' 23:59:59');
        foreach ($cajas as $caja) {
            $compras_copy_1 = clone $compras;
            $compras_copy_2 = clone $compras;
            $compras_copy_3 = clone $compras;
            $ventas_copy_1 = clone $ventas;
            $ventas_copy_2 = clone $ventas;
            $ventas_copy_3 = clone $ventas;
            $caja->_totalCompras = $compras_copy_1->where('caja_id',$caja->id)
                ->where('tipo','co')
                ->get()->sum('total');
            $pagos = 0;
            $compras_copy_2 = $compras_copy_2->where('caja_id',$caja->id)
                ->where('tipo','co')
                ->get();
            foreach ($compras_copy_2 as $value) {
                $pagos += $value->pagos->sum('cuota');
            }
            $caja->_totalCompras -= $pagos;
            /*$caja->_totalCreditosCompras = $compras_copy_2->where('caja_id',$caja->id)
                ->where('tipo','cr')
                ->get()->sum('total');*/
            $compras_copy_3 = $compras_copy_3->where('caja_id',$caja->id)
                ->where('tipo','cr')
                ->get();
            $total = 0;
            foreach ($compras_copy_3 as $compra) {
                $total += $compra->pagos()->where('pagos.fecha_hora','>',$date . ' 23:59:59')->get()->sum('cuota');
            }
            $caja->_totalPagosCompras = $total;

            $caja->_totalVentas = $ventas_copy_1->where('caja_id',$caja->id)
                ->where('tipo','co')
                ->get()->sum('total');
            /*$caja->_totalCreditosVentas = $ventas_copy_2->where('caja_id',$caja->id)
                ->where('tipo','cr')
                ->get()->sum('total');*/
            $ventas_copy_3 = $ventas_copy_3->where('caja_id',$caja->id)
                ->where('tipo','cr')
                ->get();
            $total = 0;
            foreach ($ventas_copy_3 as $venta) {
                $total += $venta->pagos->sum('cuota');
            }
            $caja->_totalPagosVentas = $total;

            $_caja = $caja->cuentas()
                ->wherePivot('fecha_hora','>',$date . ' 23:59:59');
            $caja_copy_1 = clone $_caja;
            $caja_copy_2 = clone $_caja;

            $caja->_totalDepositos = $caja_copy_1->wherePivot('tipo','d')->get()->sum('pivot.monto');
            $caja->_totalRetiros = $caja_copy_2->wherePivot('tipo','r')->get()->sum('pivot.monto');
        }
        $outputData = [];
        foreach ($cajas as $caja) {
            $ganancias = $caja->_totalVentas+$caja->_totalPagosVentas;
            $inversion = $caja->_totalCompras+$caja->_totalPagosCompras;
            $efectivo_ajustado_fecha = $caja->efectivo - $ganancias + $inversion + $caja->_totalRetiros - $caja->_totalDepositos;

            $comprasEfectivo = $caja->totalCompras + $caja->totalPagosCompras;
            $ventasEfectivo = $caja->totalVentas + $caja->totalPagosVentas;
            $prepareOutputData = [
                'compras' => $comprasEfectivo,
                'ventas' => $ventasEfectivo,
                'depositos' => $caja->totalDepositos,
                'retiros' => $caja->totalRetiros,
                'efectivo_inicio_caja' => $efectivo_ajustado_fecha-$ventasEfectivo
                    +$comprasEfectivo-$caja->totalDepositos+$caja->totalRetiros,
                'efectivo_caja' => $efectivo_ajustado_fecha,
            ];
            array_push($outputData, $prepareOutputData);
        }
//        $cajas->comprasVentas;
        /*$compras = CompraVenta::join('compras', 'compras.id_compra_venta', '=', 'compras_ventas.id')
            ->whereBetween('fecha_hora', [$date . ' 00:00:00', $date . ' 23:59:59']);*/
        /*$cajas = Caja::join('compras_ventas','compras_ventas.caja_id', '=', 'cajas.id')
            ->where('nulo',0)
            ->whereBetween('fecha_hora', [$date . ' 00:00:00', $date . ' 23:59:59']);*/
        return response()->json($outputData);
    }
    public function calculateCashClosing($cajaId, $date) {
        $caja = Caja::find($cajaId);
        $caja->fecha = $date;

        $compras = CompraVenta::join('compras', 'compras.id_compra_venta', '=', 'compras_ventas.id')
            ->where('caja_id', $cajaId)
            ->where('tipo', 'co')
            ->whereBetween('fecha_hora', [$date . ' 00:00:00', $date . ' 23:59:59'])
            ->get();
        $caja->totalCompras = $compras->sum('total');
        $totalDescuentosCompras = $compras->sum('descuento');

        $caja->totalPagosCompras = Pago::whereBetween('pagos.fecha_hora', [$date . ' 00:00:00', $date . ' 23:59:59'])
            ->join('compras', 'compras.id_compra_venta', '=', 'pagos.compra_venta_id')
            ->join('compras_ventas', 'compras_ventas.id', '=', 'pagos.compra_venta_id')
            ->where('compras_ventas.caja_id', $cajaId)
            ->where('compras_ventas.tipo', 'cr')
            ->get()->sum('cuota');

        $comprasNulas = CompraVenta::join('compras', 'compras.id_compra_venta', '=', 'compras_ventas.id')
            ->where('caja_id', $cajaId)
            ->where('nulo', 1)
            ->whereBetween('fecha_nulo', [$date . ' 00:00:00', $date . ' 23:59:59']);
        $compras_nulo_copy = clone $comprasNulas;
        $totalComprasNulas = $compras_nulo_copy->where('tipo', 'co')->get()->sum('total');
        $compras_nulo_copy = clone $comprasNulas;
        $totalDescuentosComprasNulas = $compras_nulo_copy->where('tipo', 'co')->get()->sum('descuento');

        $pagos = 0;
        $compras_nulo_copy = clone $comprasNulas;
        $compras_nulo_copy = $compras_nulo_copy->where('tipo', 'cr');
        foreach ($compras_nulo_copy->get() as $venta) {
            $pagos += Pago::where('compra_venta_id', $venta->id)->get()->sum('cuota');
        }
        $totalPagosComprasNulas = $pagos;

        $caja->totalCompras = $caja->totalCompras -  $totalDescuentosCompras;
        $caja->totalComprasNulas = $totalComprasNulas + $totalPagosComprasNulas - $totalDescuentosComprasNulas;
        //------------------ Ventas --------------------
        $ventas = CompraVenta::join('ventas', 'ventas.id_compra_venta', '=', 'compras_ventas.id')
            ->where('caja_id', $cajaId)
            ->where('tipo', 'co')
            ->whereBetween('fecha_hora', [$date . ' 00:00:00', $date . ' 23:59:59'])
            ->get();
        $caja->totalVentas = $ventas->sum('total');
        $totalDescuentosVentas = $ventas->sum('descuento');

        $caja->totalPagosVentas = Pago::whereBetween('pagos.fecha_hora', [$date . ' 00:00:00', $date . ' 23:59:59'])
            ->join('ventas', 'ventas.id_compra_venta', '=', 'pagos.compra_venta_id')
            ->join('compras_ventas', 'compras_ventas.id', '=', 'pagos.compra_venta_id')
            ->where('compras_ventas.caja_id', $cajaId)
            ->where('compras_ventas.tipo', 'cr')
            ->get()->sum('cuota');
        $ventasNulas = CompraVenta::join('ventas', 'ventas.id_compra_venta', '=', 'compras_ventas.id')
            ->where('caja_id', $cajaId)
            ->where('nulo', 1)
            ->whereBetween('fecha_nulo', [$date . ' 00:00:00', $date . ' 23:59:59']);

        $ventas_nulo_copy = clone $ventasNulas;
        $totalVentasNulas = $ventas_nulo_copy->where('tipo', 'co')->get()->sum('total');
        $ventas_nulo_copy = clone $ventasNulas;
        $totalDescuentosVentasNulas = $ventas_nulo_copy->where('tipo', 'co')->get()->sum('descuento');
        $totalVentasNulas -=$totalDescuentosVentasNulas;

        $pagos = 0;
        $ventas_nulo_copy = clone $ventasNulas;
        $ventas_nulo_copy = $ventas_nulo_copy->where('tipo', 'cr');
        foreach ($ventas_nulo_copy->get() as $venta) {
            $pagos += Pago::where('compra_venta_id', $venta->id)->get()->sum('cuota');
        }
        $totalPagosVentasNulas = $pagos;

        $caja->totalVentas = $caja->totalVentas -  $totalDescuentosVentas;
        $caja->totalVentasNulas = $totalVentasNulas + $totalPagosVentasNulas;

        //---------------Caja depositos y retiros
        $_caja = $caja->cuentas()
            ->wherePivot('fecha_hora', '>=', $date . ' 00:00:00')
            ->wherePivot('fecha_hora', '<=', $date . ' 23:59:59');
        $caja_copy_1 = clone $_caja;
        $caja_copy_2 = clone $_caja;

        $caja->totalDepositos = $caja_copy_1->wherePivot('tipo', 'd')->get()->sum('pivot.monto');
        $caja->totalRetiros = $caja_copy_2->wherePivot('tipo', 'r')->get()->sum('pivot.monto');

        $lastDate = CierreDeCaja::where('caja_id', $cajaId)->max('fecha');
        $cierreCaja = CierreDeCaja::where('fecha', $lastDate)->first();
        if (!is_null($cierreCaja)) {
            $efectivo_incial = $cierreCaja->efectivo_final;
        } else {
            $efectivo_incial =
                $caja->totalVentas + $caja->totalPagosVentas + $caja->totalDepositos + $caja->totalComprasNulas
                - $caja->totalCompras - $caja->totalPagosCompras - $caja->totalRetiros - $caja->totalVentasNulas;
        }
        $caja->efectivo_inicial = $efectivo_incial;

        return $caja;
    }
    public function storeCashClosing() {
        $caja = Caja::where('ip', request()->ip())->first();
        $today = Carbon::now();
        if (is_null($caja)) {
            return response()->json('Este equipo no está asignado a ninguna caja',400);
        }
//        $cierreCaja = CierreDeCaja::where('fecha',$date)->where('caja_id',$caja->id)->first();
        $cierreCajaDate = CierreDeCaja::where('caja_id',$caja->id)->max('fecha');
        $cierreCaja = CierreDeCaja::where('fecha',$cierreCajaDate)->first();
        if (!is_null($cierreCaja)) {
            /*$splitDate = explode("-", $cierreCajaDate);
            $date = Carbon::createFromDate($splitDate[0], $splitDate[1], $splitDate[2] + 1);
            $date = $date->format('Y-m-d');*/
            if ($cierreCajaDate == $today->format('Y-m-d')) {
                $cierreCaja->delete();
                $oldDate = Carbon::parse($cierreCajaDate);
            } else {
                $oldDate = Carbon::parse($cierreCajaDate)->addDay();
            }
        } else {
            /*si en la base de datos, cierre_cajas no existe ningun registro se tomara la primera fecha
            del primera registro de comoras_ventas*/
            $getDate = CompraVenta::min('fecha_hora');
            $oldDate = Carbon::parse($getDate);
        }
        $aux =[];
        $today = $today->format('Y-m-d');
        $today = Carbon::parse($today . ' 23:59:59');
        while ($oldDate <= $today) {

            $dataCashClosing = $this->calculateCashClosing($caja->id, $oldDate->format('Y-m-d'));
            array_push($aux, $dataCashClosing);
            $cierreCaja = new CierreDeCaja();
            $cierreCaja->fecha = $oldDate->format('Y-m-d');
            $cierreCaja->efectivo_inicial = $dataCashClosing->efectivo_inicial;
            $cierreCaja->compras = $dataCashClosing->totalCompras;
            $cierreCaja->pagos_compras = $dataCashClosing->totalPagosCompras;
            $cierreCaja->compras_nulas = $dataCashClosing->totalComprasNulas;
            $cierreCaja->ventas = $dataCashClosing->totalVentas;
            $cierreCaja->pagos_ventas = $dataCashClosing->totalPagosVentas;
            $cierreCaja->ventas_nulas = $dataCashClosing->totalVentasNulas;
            $cierreCaja->depositos = $dataCashClosing->totalDepositos;
            $cierreCaja->retiros = $dataCashClosing->totalRetiros;
            $cierreCaja->efectivo_final = $dataCashClosing->efectivo_inicial
                + $dataCashClosing->totalVentas + $dataCashClosing->totalPagosVentas + $dataCashClosing->totalDepositos
                - $dataCashClosing->totalCompras - $dataCashClosing->totalPagosCompras - $dataCashClosing->totalRetiros;

            $cierreCaja->efectivo_final = $cierreCaja->efectivo_final
                + $dataCashClosing->totalComprasNulas - $dataCashClosing->totalVentasNulas;

            $caja->cierresDeCajas()->save($cierreCaja);
            $oldDate->addDay();
        }
        return response()->json(null);
    }
    public function getCashClosing($date1,$date2) {
        if(!($date1 && $date2)) {
            return response()->json('Las fechas son requidiras',400);
        }
        $cashClosure = CierreDeCaja::whereBetween('fecha',[$date1,$date2])->orderBy('fecha', 'desc')->get();
        foreach ($cashClosure as $value) {
            $caja = Caja::find($value->caja_id);
            $value->caja = ['descripcion' => $caja->descripcion];
        }
        return response()->json($cashClosure);
    }
}
