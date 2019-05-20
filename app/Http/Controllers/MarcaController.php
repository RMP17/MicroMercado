<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Resources\MarcasCollection;
use App\Http\Resources\MarcaResource;
use App\Producto;
use App\Marca;
use Validator;

class MarcaController extends Controller
{
    public function __construct()
    {
        $this->middleware('is_admin', ['only' => ['update']]);
    }
    public function index()
    {
        return (new MarcasCollection(Marca::all()))->additional([
            'status' => 200,
        ]);
    }

    public function store(Request $request)
    {
        $data = json_decode($request->json, true);

        $validator = Validator::make($data, [
            'nombre' => ['string', 'required'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
        $marca = new Marca();
        $marca->fill($data);
        $marca->save();

        return response()->json(null, 200);
    }

    public function update(Request $request, $id)
    {
        $data = json_decode($request->json, true);
        $validator = Validator::make($data, [
            'nombre' => ['string', 'required'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $marca = Marca::find($id);
        $marca->fill($data);
        $marca->save();
        return response()->json(null, 200);
    }
    public function show($id)
    {
        try {
            $producto = Producto::findOrFail($id);
            return (MarcaResource::collection($producto->marcas))->additional([
                'status' => 200
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json($e, 400);
        }
    }
}
