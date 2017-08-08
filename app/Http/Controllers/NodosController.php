<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use \App\Nodo;

class NodosController extends Controller
{
    public function index() {
        return response()->view('nodos');
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

        if($n->save())
            return redirect()->route('nodos.index');

    }


    public function list_em() {
        return response()->json([
            'data'=>Nodo::all()->toArray()
        ]);        
    }

    public function borrar($id) {
        if(Nodo::findOrFail($id)->delete())
            return response()->json(['status'=>'ok']);

        return response()->json(['status'=>'err']);
    }

    public function agregar_aparato($id,$id_aparato) {
        $a=\App\Aparato::findOrFail($id_aparato);
        $n=Nodo::findOrFail($id);

        $a->nodo_id=$n->id;

        if($a->save())
            return response()->json(['status'=>'ok']);

        return response()->json(['status'=>'err']);
    }

    public function quitar_aparato($id) {
        $a=\App\Aparato::findOrFail($id);

        $a->nodo_id=0;

        if($a->save())
            return response()->json(['status'=>'ok']);

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
