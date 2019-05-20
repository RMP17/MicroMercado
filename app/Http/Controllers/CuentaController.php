<?php

namespace App\Http\Controllers;

use App\Http\Resources\SessionResource;
use App\Sesion;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Resources\CuentaResource;
use App\Cuenta;
use App\Persona;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Validator;

class CuentaController extends Controller
{
    public function __construct()
    {
        $this->middleware('is_admin', ['except' => ['login', 'logout']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return (CuentaResource::collection(Cuenta::with('personas')->get()))->additional([
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
        try {
            $data = json_decode($request->json, true);
            $validator = Validator::make($data, [
                'id_persona' => 'required|exists:personas,id',
                'contrasenia' => 'required',
                'nivel_acceso' => 'required',
                'permisos' => 'required',
                'habilitada' => 'required'
            ]);
            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }
            $persona = Persona::find($data['id_persona']);
            $cuenta = new Cuenta();
            $cuenta->fill($data);
            $cuenta->id_persona = $data['id_persona'];
            $cuenta->usuario = $persona->ci;
            $cuenta->pass_sin_encriptar = $data['contrasenia'];
            $permisos='';
            if($data['permisos']['option1']) $permisos= '0';
            if($data['permisos']['option2']) $permisos= $permisos.'1';
//            $cuenta->permisos = implode("", $data['permisos']);
            $cuenta->permisos = $permisos;
            $cuenta->save();
            return response()->json(null, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json($e, 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
            $validator = Validator::make($data, [
                'id_persona' => 'required|exists:personas,id',
                'nivel_acceso' => 'required',
                'permisos' => 'required',
                'habilitada' => 'required'
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }
            $cuenta = Cuenta::findOrFail($id);
            $cuenta->fill($data);
            $permisos='';
            if($data['permisos']['option1']) $permisos= '0';
            if($data['permisos']['option2']) $permisos= $permisos.'1';
            $cuenta->permisos = $permisos;
            $cuenta->save();
            return response()->json(['status' => 200], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json($e, 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $cuenta = Cuenta::findOrFail($id);
            $cuenta->delete();
            return response()->json(['status' => 200], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json($e, 400);
        }
    }

    public function login(Request $request)
    {
		
        $cuenta = Cuenta::first();
        if(is_null($cuenta)) {
            $persona = [
                'ci' => '00000000',
                'nombre' => 'Admin',
                'telefono' => '',
                'direccion' => '',
                'empleado' => 1,
            ];
            $newPersona = new Persona();
            $newPersona->id = 1;
            $newPersona->fill($persona);
            $newPersona->save();
            $user = [
                'usuario'=> 'admin',
                'contrasenia'=> 'admin',
                'pass_sin_encriptar'=> 'admin',
                'nivel_acceso'=> '0',
                'permisos'=> '01',
                'habilitada' => '1'
            ];
            $newCuenta = new Cuenta();
            $newCuenta->fill($user);
            $newPersona->cuentas()->save($newCuenta);
        }
        if (Auth::attempt(['usuario' => $request->usuario, 'password' => $request->contrasenia,'habilitada' => 1])) {
            $today = Carbon::now();
            $session = new Sesion();
            $session->inicio_fecha_hora = $today;
            $user = Auth::user();
            $user->sesiones()->save($session);
            $user->nombre = $user->personas->nombre;
            $token = $user->createToken('micro')->accessToken;
            $user = [
                'id_persona' => $user->id_persona,
                'nombre' => $user->nombre,
                'permisos' => $user->permisos,
                'nivel_acceso' => $user->nivel_acceso
            ];
            return response()->json([
                'token' => $token,
                'user' => $user
            ], 200);
        } else {
            return response()->json(['error' => 'Account Unauthorised'], 401);
        }
//        }
    }
    public function logout() {
        DB::beginTransaction();
        $user = Auth::user();
        $today = Carbon::now();
        $session = Sesion::where('cuenta_id',$user->id_persona)->where('fin_fecha_hora',null)
            ->orderBy('inicio_fecha_hora','desc')->first();
        $session->fin_fecha_hora = $today;
        $session->save();
        $user->token()->delete();
        $tokens = $user->tokens;
        foreach ($tokens as $token) {
            $token->delete();
        }
        DB::commit();
        return response()->json(null,200);
    }
    public function showSessions($date1, $date2)
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
        $sessions = Sesion::whereBetween('inicio_fecha_hora', [$date1.' 00:00:00',$date2.' 23:59:59'])
            ->orderBy('inicio_fecha_hora', 'desc')
            ->get();

        return (SessionResource::collection($sessions));
    }
}
