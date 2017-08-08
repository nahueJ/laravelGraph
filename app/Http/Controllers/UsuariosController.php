<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use \App\User;
use Hash;

class UsuariosController extends Controller
{
    public function index() {
        return response()->view('usuarios');
    }

    public function editar($id) {
        return response()->view('usuarios_form',[
            'a'=>User::findOrFail($id),
            'action'=>'user_actualizar',
            'title'=>'Editar User'
        ]);
    }
    public function nuevo() {
        return response()->view('usuarios_form',[
            'a'=>null,
            'action'=>'user_crear',
            'title'=>'Nuevo User'
        ]);
    }

    public function crear(Request $request) {
        $this->validate($request, [
            'nombre'=>'required|max:255',
            'rol'=>'required|integer',
            'email'=>'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed', 
            'password_confirmation' => 'required|min:6',
        ]);

        $a=new User;

        $a->name=$request->input('nombre');
        $a->role=$request->input('rol');
        $a->password=Hash::make($request->input('password'));
        $a->email=$request->input('email');

        if($a->save())
            return redirect()->route('users.index');

    }

    public function actualizar(Request $request) {
        $this->validate($request, [
            'id'=>'required|integer',
            'nombre'=>'required|max:255',
            'rol'=>'required|integer',
            'email'=>'sometimes|required|email|unique:users,email,'.$request->input('id'),
            'password' => 'min:6|confirmed', 
            'password_confirmation' => 'min:6',
        ]);

        $a=User::findOrFail($request->input('id'));

        $a->name=$request->input('nombre');
        $a->role=$request->input('rol');

        if($request->input('password'))
            $a->password=Hash::make($request->input('password'));

        $a->email=$request->input('email');

        if($a->save())
            return redirect()->route('users.index');

    }


    public function list_em() {
        return response()->json([
            'data'=>User::all()->toArray()
        ]);        
    }

    public function borrar($id) {
        if(User::findOrFail($id)->delete())
            return response()->json(['status'=>'ok']);

        return response()->json(['status'=>'err']);
    }
}
