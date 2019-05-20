<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Resources\CodigosCollection;
use App\Codigo;
use Validator;

class CodigoController extends Controller
{
    public function __construct()
    {
        $this->middleware('is_admin', ['only' => ['destroy']]);
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
            'codigo' => ['required']
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
        $codigo = new Codigo();
        $codigo->fill($data);
        $codigo->save();
        return response()->json(['statys' => 200], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
            $codigos = Codigo::where('producto_id', $id)->get();
            if (!is_null($codigos)) {
                return (new CodigosCollection($codigos))->additional([
                    'status' => 200
                ]);
            }
            return response()->json(null, 400);
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
            Codigo::findOrFail($id)->delete();
            return response()->json(null, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json($e, 400);
        }
    }
}
