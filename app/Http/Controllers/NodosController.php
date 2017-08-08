<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use \App\Nodo;
use \App\Aparato;

class NodosController extends Controller
{
    public function index() {
		
		$total_conexiones = Nodo::all()->sum('conexionesPropias');
		
        return response()->view('nodos', compact('total_conexiones'));
    }

    public function editar($id) {
        return response()->view('nodos_form',[
            'a'=>Nodo::findOrFail($id),
            'action'=>'nodo_actualizar',
            'title'=>'Editar Nodo'
        ]);
    }
    
	public function nuevo() {
        return response()->view('nodos_form',[
            'a'=>null,
            'action'=>'nodo_crear',
            'title'=>'Nuevo Nodo'
        ]);
    }

    public function crear(Request $request) {
        $this->validate($request, [
            'nombre'=>'required|unique:nodos|max:255',
            'coordenadas'=>'required|max:255'
        ]);

        $n=new Nodo;

        $n->nombre=$request->input('nombre');
        $n->coordenadas=$request->input('coordenadas');
		
		if($request->input('root')){
			$n->jerarquia = 0;
		}
		
        if($n->save())
            return redirect()->route('nodos.index');

    }

    public function actualizar(Request $request) {
        $this->validate($request, [
            'id'=>'required|integer',
            'nombre'=>'sometimes|required|unique:nodos,nombre,'.
            $request->input('id').'|max:255',
            'coordenadas'=>'required|max:255'
        ]);

        $n=Nodo::findOrFail($request->input('id'));

        $n->nombre=$request->input('nombre');
        $n->coordenadas=$request->input('coordenadas');
		
		if($request->input('root')){
			$n->jerarquia = 0;
		}

        if($n->save()){
			app('\App\Console\Commands\CalcularPeso')->handle();
            return redirect()->route('nodos.index');
		}
    }

    public function list_em() {
        return response()->json([
            'data'=>Nodo::all()->toArray()
        ]);        
    }

    public function borrar($id)
	{
		$n = Nodo::findOrFail($id);
		$childs = $n->getChilds();
		$n->delAparatos();
        if($n->delete()){
			foreach ($childs as $nodo){
				$nodo->actualizarJerarquia();
			}
			app('\App\Console\Commands\CalcularPeso')->handle();
			return response()->json(['status'=>'ok']);
		}
        return response()->json(['status'=>'err']);
    }

    public function agregar_aparato($id,$id_aparato)
	{
        $a=\App\Aparato::findOrFail($id_aparato);
        $n=Nodo::findOrFail($id);

        $a->nodo_id=$n->id;

        if($a->save())
		{
			if ($a->rol == 2){
				$conexionActiva = $a->conectarInterfaces();
				if($conexionActiva == 1){
					$n->actualizarJerarquia();
				}
				
			}
			app('\App\Console\Commands\CalcularPeso')->handle();
            return response()->json(['status'=>'ok']);
		}
        return response()->json(['status'=>'err']);
    }

    public function quitar_aparato($id) {
        $a = \App\Aparato::findOrFail($id);
		$n = \App\Nodo::findOrFail($a->nodo_id);

		unset($a->nodo_id);
		if($a->rol == 2){
			$a->desconectarInterfaces();
			$n->actualizarJerarquia();
			app('\App\Console\Commands\CalcularPeso')->handle();
		}
        if($a->save()){
            return response()->json(['status'=>'ok']);
		}
        return response()->json(['status'=>'err']);
    }
    
	public function get_ptps($id) {

        if(!is_numeric($id))
            return response()->json(['data'=>[]]);

        return response()->json([
            'data'=>\App\Aparato::where('nodo_id',$id)
            ->where('rol',1)
            ->get()
            ->toArray()
        ]);
    }

    public function get_routers($id) {

        if(!is_numeric($id))
            return response()->json(['data'=>[]]);

        return response()->json([
            'data'=>\App\Aparato::where('nodo_id',$id)
            ->where('rol',2)
            ->get()
            ->toArray()
        ]);
    }

    public function get_switches($id) {

        if(!is_numeric($id))
            return response()->json(['data'=>[]]);

        return response()->json([
            'data'=>\App\Aparato::where('nodo_id',$id)
            ->where('rol',3)
            ->get()
            ->toArray()
        ]);
    }

    public function get_aps($id) {
        if(!is_numeric($id))
            return response()->json(['data'=>[]]);

        return response()->json([
            'data'=>\App\Aparato::where('nodo_id',$id)
            ->where('rol',4)
            ->get()
            ->toArray()
        ]);
    }
	
}
