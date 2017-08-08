<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::get('/', function () {
    return redirect('/home'); 
});

Auth::routes();

Route::group(['middleware' => 'auth'], function () {
    Route::get('/home/{trigger?}', 'HomeController@index');
    Route::get('/stats/{id}','HomeController@stats');

    Route::get('/aparatos', ['as'=>'aparatos.index','uses'=>'AparatosController@index']);
    Route::get('/aparatos_list', 'AparatosController@list_em');
    Route::get('/aparatos_unassigned_list', 'AparatosController@list_em_unassigned');
    Route::get('/aparato_nuevo', 'AparatosController@nuevo');
    Route::post('/aparato_crear','AparatosController@crear');
    Route::delete('/aparato/{id}','AparatosController@borrar');
    Route::get('/aparato/{id}','AparatosController@editar');
    Route::post('/aparato_actualizar','AparatosController@actualizar');
    Route::post('/ping','AparatosController@ping_host');
    Route::post('/portopen','AparatosController@port_open');

    Route::get('/nodos', ['as'=>'nodos.index','uses'=>'NodosController@index']);
    Route::get('/nodos_list', 'NodosController@list_em');
    Route::get('/nodo_nuevo', 'NodosController@nuevo');
    Route::post('/nodo_crear','NodosController@crear');
    Route::delete('/nodo/{id}','NodosController@borrar');
    Route::get('/nodo/{id}','NodosController@editar');
    Route::post('/nodo_actualizar','NodosController@actualizar');
    Route::post('/nodo_add_aparato/{id}/{id_aparato}','NodosController@agregar_aparato');
    Route::delete('/nodo_del_aparato/{id}','NodosController@quitar_aparato');
    Route::get('/nodo_ptps/{id}','NodosController@get_ptps');
    Route::get('/nodo_routers/{id}','NodosController@get_routers');
    Route::get('/nodo_switches/{id}','NodosController@get_switches');
    Route::get('/nodo_aps/{id}','NodosController@get_aps');

    Route::get('/usuarios', ['as'=>'users.index','uses'=>'UsuariosController@index']);
    Route::get('/users_list', 'UsuariosController@list_em');
    Route::get('/user_nuevo', 'UsuariosController@nuevo');
    Route::post('/user_crear','UsuariosController@crear');
    Route::delete('/user/{id}','UsuariosController@borrar');
    Route::get('/user/{id}','UsuariosController@editar');
    Route::post('/user_actualizar','UsuariosController@actualizar');

    Route::post('/generatestats','HomeController@generate_stats');
	
	Route::get('/peso/nodos','PesoController@nodosIndex');
	Route::post('/peso/nodos','PesoController@actNodosIndex');
	Route::get('/peso/interfaces','PesoController@interfaces');
});
