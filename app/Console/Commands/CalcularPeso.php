<?php

namespace App\Console\Commands;
use App\Nodo;
use Illuminate\Console\Command;

class CalcularPeso extends Command
{
    protected $signature = 'solucion:calcular_peso';
	protected $description = 'Calcula las conexiones dependientes de cada nodo';
	public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
		$ns = Nodo::whereNotNull('jerarquia')->get();
		$order = $ns->sortByDesc('jerarquia');
		
		foreach ($order as $n) {
			$n->updateConexiones();
			$n->save();
		}
    }
}
