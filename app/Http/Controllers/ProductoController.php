<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Resources\PersonaResource;
use App\Codigo;
use App\Producto;
use App\Http\Resources\ProductosCollection;
use App\Http\Resources\ProductoResource;
use App\Http\Resources\CategoriaResource;
use Carbon\Carbon;
use App\Configuracion;
use Validator;


class ProductoController extends Controller
{
    public function __construct()
    {
        $this->middleware('is_admin', ['only' => ['update','deleteProductoMarca','deleteProductoCategorias']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $productos = Producto::with('codigos')->get();
//        return response()->json($productos);
//        return ProductoResource::collection($productos);
//        return new ProductosCollection($productos)
        return (new ProductosCollection($productos))->additional([
            'status' => 200
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = json_decode($request->json, true);
        $data['cantidad_almacen'] = $data['cantidad_almacen'] ? $data['cantidad_almacen']:0;
        $data['stock'] = $data['stock'] ? $data['stock']:0;
        $data['fecha_caducidad'] = $data['fecha_caducidad'] ? $data['fecha_caducidad']:null;
        $data['fecha_caducidad_almacen'] = $data['fecha_caducidad_almacen'] ? $data['fecha_caducidad_almacen']:null;

        $validator = Validator::make($data, [
            'codigo' => ['unique:codigos,codigo', 'required'],
            'descripcion' => ['string', 'required'],
            'tipo_unidad' => ['string', 'required'],
            'stock' => ['numeric', 'min:0', 'required'],
            'precio_compra_unidad' => ['numeric', 'min:0', 'required'],
            'precio_venta_unidad' => ['numeric', 'min:0', 'required'],
            'cantidad_almacen' => ['numeric', 'min:0'],
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $data['cantidad_almacen'] = $data['cantidad_almacen'] ? $data['cantidad_almacen']:0;

        $producto = new Producto();
        $producto->fill($data);
        $producto->save();
        $codigos = new Codigo();
        $codigos->codigo = $data['codigo'];
        $producto->codigos()->save($codigos);

        if (isset($data['id_marca']))
            $producto->marcas()->attach($data['id_marca']);
        if (isset($data['id_categoria']))
            $producto->categorias()->attach($data['id_categoria']);
        if (isset($data['id_proveedor']))
            $producto->proveedores()->attach($data['id_proveedor']);

        return response()->json($producto->id, 200);
    }

    /** ostria resyes entre la colon y la ariaga
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (strlen($id) > 0) {
            $codigo = Codigo::where('codigo', $id)->first();
            if (!is_null($codigo)) {
                return (new ProductoResource($codigo->productos))->additional([
                    'status' => 200
                ]);
            }
        }
        return response()->json(null, 400);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $data = json_decode($request->json, true);
            $data['cantidad_almacen'] = $data['cantidad_almacen'] ? $data['cantidad_almacen']:0;
            $data['stock'] = $data['stock'] ? $data['stock']:0;
            $data['fecha_caducidad'] = $data['fecha_caducidad'] ? $data['fecha_caducidad']:null;
            $data['fecha_caducidad_almacen'] = $data['fecha_caducidad_almacen'] ? $data['fecha_caducidad_almacen']:null;
            $validator = Validator::make($data, [
                'descripcion' => ['string', 'required'],
                'tipo_unidad' => ['string', 'required'],
                'stock' => ['numeric', 'min:0', 'required'],
                'precio_compra_unidad' => ['numeric', 'min:0', 'required'],
                'precio_venta_unidad' => ['numeric', 'min:0', 'required'],
                'cantidad_almacen' => ['numeric', 'min:0', 'nullable'],
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }
            $producto = Producto::findOrFail($id);
            $data['cantidad_almacen'] = $data['cantidad_almacen'] ? $data['cantidad_almacen'] : 0;
            $data['fecha_caducidad_almacen'] = $data['fecha_caducidad_almacen'] ? $data['fecha_caducidad_almacen'] : null;
            $producto->fill($data);
            $producto->save();

            return (new ProductoResource($producto))->additional([
                'status' => 200
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json($e, 400);
        }
    }

    public function transferirProductos(Request $request, $id)
    {
        try {
            $producto = Producto::findOrFail($id);
            $data = json_decode($request->json, true);
            $validator = Validator::make($data, [
                'cantidad' => ['numeric', 'min:0'],
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }
            if ($producto->cantidad_almacen - $data['cantidad'] >= 0) {
                $producto->stock += $data['cantidad'] ? $data['cantidad'] : 0;
                $producto->cantidad_almacen -= $data['cantidad'] ? $data['cantidad'] : 0;
                $producto->save();
            } else {
                return response()->json('La cantidad debe ser menor o igual a la cantidad del almacén', 400);
            }
            return response()->json(['status' => 200],200);
        } catch (ModelNotFoundException $e) {
            return response()->json($e, 400);
        }
    }

    public function showProductoId($id)
    {
        if (strlen($id) > 0) {
            $producto = Producto::find($id)->first();
            if (!is_null($producto)) {
                return (new ProductoResource($producto))->additional([
                    'status' => 200
                ]);
            }
        }
        return response()->json(null, 200);
    }

    public function storeProductoMarca(Request $request)
    {

        try {
            $data = json_decode($request->json, true);
            $validator = Validator::make($data, [
                'id_producto' => ['required'],
                'id_marca' => ['required']
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }
            $producto = Producto::findOrFail($data['id_producto']);
            $productoMarca = $producto->marcas()->where('id_marca', $data['id_marca'])->first();
            if (is_null($productoMarca)) {
                $producto->marcas()->attach($data['id_marca']);
            }
            return response()->json(null, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json($e, 400);
        }

    }

    public function deleteProductoMarca(Request $request)
    {
        try {
            $data = json_decode($request->json, true);
            $validator = Validator::make($data, [
                'id_producto' => ['required'],
                'id_marca' => ['required']
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }
            $producto = Producto::findOrFail($data['id_producto']);
            $productoMarca = $producto->marcas()->where('id_marca', $data['id_marca'])->first();
            if (!is_null($productoMarca)) {
                $producto->marcas()->detach($data['id_marca']);;
            }
            return response()->json(null, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json($e, 400);
        }

    }

    public function showProductoCategorias($id)
    {
        $producto = Producto::findOrFail($id);
        if (!is_null($producto)) {
            return (CategoriaResource::collection($producto->categorias))->additional([
                'status' => 200
            ]);
        }
        return response()->json(null, 400);
    }

    public function storeProductoCategorias(Request $request)
    {

        try {
            $data = json_decode($request->json, true);
            $validator = Validator::make($data, [
                'id_producto' => ['required'],
                'id_categoria' => ['required']
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }
            $producto = Producto::findOrFail($data['id_producto']);
            $productoCategoria = $producto->categorias()->where('id_categoria', $data['id_categoria'])->first();
            if (is_null($productoCategoria)) {
                $producto->categorias()->attach($data['id_categoria']);
            }
            return response()->json(null, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json($e, 400);
        }

    }

    public function deleteProductoCategorias(Request $request)
    {
        try {
            $data = json_decode($request->json, true);
            $validator = Validator::make($data, [
                'id_producto' => ['required'],
                'id_categoria' => ['required']
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }
            $producto = Producto::findOrFail($data['id_producto']);
            $productoMarca = $producto->categorias()->where('id_categoria', $data['id_categoria'])->first();
            if (!is_null($productoMarca)) {
                $producto->categorias()->detach($data['id_categoria']);;
            }
            return response()->json(null, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json($e, 400);
        }

    }
    public function imageUploadPost(Request $request)
    {

            $data = $request->allFiles();
            $validator = Validator::make($data, [
                'uploads' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);
            $validator2 = Validator::make($request->all(), [
                'id_producto' => 'required'
            ]);
            if ($validator->fails() || $validator2->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            };
//            $imageTempName = $data['uploads']->getPathname();
            $imageName = $data['uploads']->getClientOriginalName();
            $path = public_path().'/images';
            $data['uploads']->move($path, $imageName);
            $producto= Producto::find($request->id_producto);
            $producto->img=$imageName;
            $producto->save();
            return response()->json($imageName, 200);
    }
    public function updateDate(Request $request, $id=null)
    {
        $data = json_decode($request->json, true);
        $validator = Validator::make($data, [
            'fecha_caducidad' => ['date_format:Y-m-d'],
            'fecha_caducidad_almacen' => ['date_format:Y-m-d']
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
        if (isset($id) && strlen($id) > 0 ){
            $producto = Producto::find($id);
            if(!is_null($producto)) {
                if(isset($data['fecha_caducidad'])) {
                    $producto->fecha_caducidad=$data['fecha_caducidad'];
                } else {
                    $producto->fecha_caducidad_almacen=$data['fecha_caducidad_almacen'];
                }
                $producto->save();
                return response()->json(['status' => 200], 200);
            }
        }
        return response()->json(['status' => 400], 400);
    }
    public function updateNotifications(Request $request, $id=null)
    {
        $data = json_decode($request->json, true);
        $validator = Validator::make($data, [
            'notificar' => ['numeric'],
            'notificar_fecha_caducidad' => ['numeric']
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
        if (isset($id) && strlen($id) > 0 ){
            $producto = Producto::find($id);
            if(!is_null($producto)) {
                if(isset($data['notificar'])) {
                    $producto->notificar=$data['notificar'];
                } else {
                    $producto->notificar_fecha_caducidad=$data['notificar_fecha_caducidad'];
                }
                $producto->save();
                return response()->json(['status' => 200], 200);
            }
        }
        return response()->json(['status' => 400], 400);
    }
    public function getProveedoresForProduct($idProducto)
    {
        try {
            $producto = Producto::findOrFail($idProducto);
            return (PersonaResource::collection($producto->proveedores))->additional([
                'status' => 200
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json($e, 400);
        }
    }
    public function getQuantityProducts()
    {
        $config = Configuracion::first();
        if(!is_null($config)) {
            $QuantityProducts = Producto::where('stock', '<=',$config->stock_min_antes_mostrar)
                ->where('notificar', 1)
                ->get()->count();
            return response()->json($QuantityProducts);
        }
        return response()->json(['errors' => 'Llene los datos de configuración'], 400);
    }
    public function getQuantityProductsToExpire()
    {
        $config = Configuracion::first();
        if(!is_null($config)) {
            $today = Carbon::now();
            $dateBeforeShowingProductsExpire = Carbon::createFromDate($today->year, $today->month,
                $today->day + $config->dias_antes_mostrar_vencimiento);
            $QuantityProductsStore = Producto::where(function ($query) use ($dateBeforeShowingProductsExpire){
                $query->where('fecha_caducidad', '<=',$dateBeforeShowingProductsExpire)
                    ->orWhere('fecha_caducidad', null);
            })
                ->where('notificar', 1)
                ->get()->count();

            $dateBeforeShowingProductsExpireOfWarehouse = Carbon::createFromDate($today->year, $today->month,
                $today->day + $config->dias_antes_mostrar_vencimiento);
            $QuantityProductsFromTheWarehouse = Producto::where(function ($query) use ($dateBeforeShowingProductsExpireOfWarehouse){
                $query->where('fecha_caducidad_almacen', '<=',$dateBeforeShowingProductsExpireOfWarehouse)
                    ->orWhere('fecha_caducidad_almacen', null);
            })
                ->where('notificar_fecha_caducidad', 1)
                ->get()->count();
            return response()->json(['tienda' => $QuantityProductsStore, 'almacen'=> $QuantityProductsFromTheWarehouse]);
        }
        return response()->json(['errors' => 'Llene los datos de configuración'], 400);
    }
    public function getLimitsToShowNotifications()
    {
        $config = Configuracion::first();
        if(!is_null($config)) {
            return response()->json(['stock' => $config->stock_min_antes_mostrar, 'dias_antes'=> $config->dias_antes_mostrar_vencimiento]);
        }
        return response()->json(['errors' => 'Llene los datos de configuración'], 400);
    }
    /*public function getImage($id)
    {

        $producto = Producto::find($id);

        if(!is_null($producto))
        {
            $file = public_path().'/uploads/images/';
            $files = File::files($file.$id);
            dd($files);
            if (File::isFile($file)) {
                $file = File::get($file);;
                return response()->make($file,200);
            }
        }
        return response()->json(null,400);
    }*/
}
