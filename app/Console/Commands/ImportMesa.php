<?php

namespace App\Console\Commands;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use App\Nodo;
use App\Aparato;

class ImportMesa extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'solucion:import_mesa';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetches data from mesa interface';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
		$db_ext = \DB::connection('mysql');
	
		$server = 'ISP06';
		
		$query = $db_ext->table('node')
				->select('server.server_id', 'server.name as server_name', 'node.node_id as node_id', 'node.name as nodo_name', DB::raw('count(*) as conexiones'))
				->join('server', 'node.server_id', '=', 'server.server_id')
				->leftJoin('connection', 'connection.node_id', '=', 'node.node_id')
				->groupBy('node.node_id','server.server_id', 'server_name', 'nodo_name')
				->orderBy('conexiones', 'desc')
				->where('server.name', '=', $server)
				->where('connection.status_account', '=', 'enabled')
				->get();
	
		/*$total_conexiones = $db_ext->table('connection')
			->select(DB::raw('count(*) as conexiones'))
			->get();

		$total_conexiones = $db_ext->table('node')
			->select(DB::raw('count(*) as conexiones'))
			->join('server', 'node.server_id', '=', 'server.server_id')
			->leftJoin('connection', 'connection.node_id', '=', 'node.node_id')
			->where('server.server_id', '=', $data)
			->where('connection.status_account', '=', 'enabled')
			->get();*/
		
		foreach($query as $nodo) {
            if(\App\Nodo::where('legacy_nodo_id',$nodo->node_id)->count()==0) {
                $n=new \App\Nodo;
                $n->nombre=$nodo->nodo_name;
                $n->coordenadas='xxx,yyy';
                $n->legacy_nodo_id=$nodo->node_id;
				$n->conexionesPropias=$nodo->conexiones;
				if($nodo->nodo_name == 'CERRO'){
					$n->jerarquia = 0;
				}
                $n->save();
            } else {
				$existNodo = \App\Nodo::where('legacy_nodo_id',$nodo->node_id)->first();
				$existNodo->conexionesPropias=$nodo->conexiones;
                $existNodo->save();
			}
        }
		
		$ns = Nodo::all();
		$as = Aparato::all();
		
		foreach ($ns as $n){
			if ($n->nombre == 'CERRO'){
				app('\App\Http\Controllers\NodosController')->agregar_aparato($n->id,$as->where('nombre','Router Cerro Arco')->first()->id);
			} elseif ($n->nombre == 'ALTOSOESTE'){
				app('\App\Http\Controllers\NodosController')->agregar_aparato($n->id,$as->where('nombre','Router Alto Oeste')->first()->id);
			} elseif ($n->nombre == 'VOLCAN'){
				app('\App\Http\Controllers\NodosController')->agregar_aparato($n->id,$as->where('nombre','Router Volcan')->first()->id);
			} elseif ($n->nombre == 'FAVORITA'){
				app('\App\Http\Controllers\NodosController')->agregar_aparato($n->id,$as->where('nombre','Router Favorita')->first()->id);
			} elseif ($n->nombre == 'PROVIDENCIA'){
				app('\App\Http\Controllers\NodosController')->agregar_aparato($n->id,$as->where('nombre','Router Providencia')->first()->id);
			} elseif ($n->nombre == 'GEOANDINA'){
				app('\App\Http\Controllers\NodosController')->agregar_aparato($n->id,$as->where('nombre','Router Geoandina')->first()->id);
			} elseif ($n->nombre == 'CHALLAO'){
				app('\App\Http\Controllers\NodosController')->agregar_aparato($n->id,$as->where('nombre','Router Challao')->first()->id);
			} elseif ($n->nombre == 'CHAMPAGNAT'){
				app('\App\Http\Controllers\NodosController')->agregar_aparato($n->id,$as->where('nombre','Router Champagnat')->first()->id);
			} elseif ($n->nombre == 'MIX'){
				app('\App\Http\Controllers\NodosController')->agregar_aparato($n->id,$as->where('nombre','Router MixFM')->first()->id);
			} elseif ($n->nombre == 'COMPAÑIA'){
				app('\App\Http\Controllers\NodosController')->agregar_aparato($n->id,$as->where('nombre','Router Compania')->first()->id);
			}
		}
		
		
		$this->call('solucion:calcular_peso');
    }
}
