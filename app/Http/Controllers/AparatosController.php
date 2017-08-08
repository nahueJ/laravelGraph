<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use \App\Aparato;
use \App\DeviceInterface;

use Illuminate\Support\Facades\DB;


class AparatosController extends Controller
{
    public function index() {
        return response()->view('aparatos');
    }

    public function editar($id) {
        return response()->view('aparatos_form',[
            'a'=>Aparato::findOrFail($id),
            'action'=>'aparato_actualizar',
            'title'=>'Editar Aparato'
        ]);
    }
    
	public function nuevo() {
        return response()->view('aparatos_form',[
            'a'=>null,
            'action'=>'aparato_crear',
            'title'=>'Nuevo Aparato'
        ]);
    }

    public function crear(Request $request) {
        $this->validate($request, [
            'nombre'=>'required|unique:aparatos|max:255',
            'rol'=>'required|integer',
            'ip'=>'required|ip',
            'puerto'=>'required|integer',
            'usuario'=>'required|max:255',
            'password'=>'required|max:255',
        ]);

        $a=new Aparato;

        $a->nombre=$request->input('nombre');
        $a->rol=$request->input('rol');
        $a->ip=$request->input('ip');
        $a->puerto=$request->input('puerto');
        $a->usuario=$request->input('usuario');
        $a->password=$request->input('password');
        $a->plataforma=$request->input('plataforma');
        $a->ubiquiti_subtype=$request->input('ubiquiti_subtype');

        if($a->save()){
			if($a->rol == 2){
				$a->charge_self_interfaces();
			}
			return redirect()->route('aparatos.index');
		}

    }

    public function actualizar(Request $request) {
        $this->validate($request, [
            'id'=>'required|integer',
            'nombre'=>'sometimes|required|unique:aparatos,nombre,'.
            $request->input('id').'|max:255',
            'rol'=>'required|integer',
            'ip'=>'required|ip',
            'puerto'=>'required|integer',
            'usuario'=>'required|max:255',
            'password'=>'required|max:255',
        ]);

        $a=Aparato::findOrFail($request->input('id'));

        $a->nombre=$request->input('nombre');
        $a->rol=$request->input('rol');
        $a->ip=$request->input('ip');
        $a->puerto=$request->input('puerto');
        $a->usuario=$request->input('usuario');
        $a->password=$request->input('password');
        $a->plataforma=$request->input('plataforma');
        $a->ubiquiti_subtype=$request->input('ubiquiti_subtype');
		
		if($a->rol == 2){
			$a->uncharge_interfaces();
		}
			
        if($a->save()){
			if($a->rol == 2){
				$a->charge_self_interfaces();
			}
            return redirect()->route('aparatos.index');
		}
    }

    public function list_em() {
        return response()->json([
            'data'=>Aparato::all()->toArray()
        ]);        
    }

    public function list_em_unassigned() {
        return response()->json([
            'data'=>Aparato::whereNull('nodo_id')->get()->toArray()
        ]);        
    }

    public function borrar($id) {
		$a = Aparato::findOrFail($id);
		
		if($a->rol == 2){
			$a->uncharge_interfaces();
		}

        if($a->delete())
            return response()->json(['status'=>'ok']);

        return response()->json(['status'=>'err']);
    }

    public function ping_host(Request $request) {
        exec('ping -c 1 '.$request->json('host'),$out,$status);

        if($status==0)
            return response()->json(['status'=>'ok']);

        return response()->json(['status'=>'err']);
    }

    public function port_open(Request $request) {

        try {
            $fp=fsockopen($request->json('host'),$request->json('port'),$errno,$errstr,10);
        }catch (\ErrorException $e) {
            return response()->json(['status'=>'err','errno'=>$errno,'errstr'=>$errstr]);
        }

        return response()->json(['status'=>'ok']);

    }
	
}