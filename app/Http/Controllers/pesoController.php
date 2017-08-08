<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests;

class pesoController extends Controller
{
	public function nodosIndex() {
		$db_ext = \DB::connection('mysql');
								
        $query = $db_ext->table('node')
            ->select('server.server_id', 'server.name as server_name', 'node.node_id', 'node.name as nodo_name', DB::raw('count(*) as conexiones'))
			->join('server', 'node.server_id', '=', 'server.server_id')
			->leftJoin('connection', 'connection.node_id', '=', 'node.node_id')
			->where('status_account', '=', 'enabled')
			->groupBy('node.node_id','server.server_id', 'server_name', 'nodo_name')
			->orderBy('conexiones', 'desc')
            ->get();
		
		$total_conexiones = $db_ext->table('connection')
            ->select(DB::raw('count(*) as conexiones'))
			->where('node_id', '<>', 'NULL')
			->where('status_account', '=', 'enabled')
            ->get();

		$servers = $db_ext->table('server')
			->select('server.server_id', 'server.name')
			->get();
		
		$data = '0';
		
		//dd($total_conexiones);
		
		return view('peso/list', compact('query', 'servers', 'data', 'total_conexiones'));
	}
	
	public function actNodosIndex() {
		$this->validate(request(), [
			'server_id' => ['required']
		]);
		$data = request()->server_id;
		//$srvid = $data->server_id;
		
		$db_ext = \DB::connection('mysql');
		
		if($data == "0")
		{
			$query = $db_ext->table('node')
				->select('server.server_id', 'server.name as server_name', 'node.node_id', 'node.name as nodo_name', DB::raw('count(*) as conexiones'))
				->join('server', 'node.server_id', '=', 'server.server_id')
				->leftJoin('connection', 'connection.node_id', '=', 'node.node_id')
				->groupBy('node.node_id','server.server_id', 'server_name', 'nodo_name')
				->orderBy('conexiones', 'desc')
				->where('connection.status_account', '=', 'enabled')
				->get();
			
			$total_conexiones = $db_ext->table('connection')
				->select(DB::raw('count(*) as conexiones'))
				->where('node_id', '<>', 'NULL')
				->where('status_account', '=', 'enabled')
				->get();
		}
		else
		{
			$query = $db_ext->table('node')
				->select('server.server_id', 'server.name as server_name', 'node.node_id', 'node.name as nodo_name', DB::raw('count(*) as conexiones'))
				->join('server', 'node.server_id', '=', 'server.server_id')
				->leftJoin('connection', 'connection.node_id', '=', 'node.node_id')
				->groupBy('node.node_id','server.server_id', 'server_name', 'nodo_name')
				->orderBy('conexiones', 'desc')
				->where('server.server_id', '=', $data)
				->where('connection.status_account', '=', 'enabled')
				->get();
			
			$total_conexiones = $db_ext->table('connection')
				->select(DB::raw('count(*) as conexiones'))
				->get();
			
			$total_conexiones = $db_ext->table('node')
				->select(DB::raw('count(*) as conexiones'))
				->join('server', 'node.server_id', '=', 'server.server_id')
				->leftJoin('connection', 'connection.node_id', '=', 'node.node_id')
				->where('server.server_id', '=', $data)
				->where('connection.status_account', '=', 'enabled')
				->get();
		}
	
		$servers = $db_ext->table('server')
			->select('server.server_id', 'server.name')
			->get();
		
		return view('peso/list', compact('query', 'servers', 'data',  'total_conexiones'));
	}
	
}
