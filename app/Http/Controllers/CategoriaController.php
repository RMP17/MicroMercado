<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoriasCollection;
use Illuminate\Http\Request;
use App\Categoria;
use Validator;

class CategoriaController extends Controller
{
    public function __construct()
    {
        $this->middleware('is_admin', ['only' => ['update']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return (new CategoriasCollection(Categoria::all()))->additional([
            'status' => 200,
        ]);
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
            'descripcion' => ['string', 'required'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
        $categoria = new Categoria();
        $categoria->fill($data);
        $categoria->save();

        return response()->json(null, 200);
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $data = json_decode($request->json, true);
        $validator = Validator::make($data, [
            'descripcion' => ['string', 'required'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $categoria = Categoria::findOrFail($id);
        $categoria->fill($data);
        $categoria->save();
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
        //
    }
}
