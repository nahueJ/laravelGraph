<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

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
        $ch = curl_init ();
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt ($ch, CURLOPT_URL, "https://mesa.westnet.com.ar/default/ingresar");
        curl_setopt ($ch, CURLOPT_POSTFIELDS, json_encode ([ 'usuario' => 'laravel', 'contrasena' => 'l4r4v3lc0d3' ]));
        curl_setopt ($ch, CURLOPT_COOKIEFILE, ""); // al pasar un string vacío guarda las cookies generadas por /default/ingresar para su uso posterior en /prueba/41
        curl_exec ($ch);
        curl_setopt ($ch, CURLOPT_URL, "https://mesa.westnet.com.ar/prueba/41");
        $datos = json_decode (curl_exec ($ch), true);
        
        foreach($datos['nodo'] as $nodo) {
            if(\App\Nodo::where('nombre',$nodo['nombre'])->count()==0) {
                $n=new \App\Nodo;
                $n->nombre=$nodo['nombre'];
                $n->coordenadas=$nodo['latitud'].','.$nodo['longitud'];
                $n->legacy_nodo_id=$nodo['codigo'];
                $n->save();
            } else {
                $n=\App\Nodo::where('nombre',$nodo['nombre'])->first();
                $n->legacy_nodo_id=$nodo['codigo'];
                $n->save();
            }
        }
        foreach($datos['ap'] as $ap) {
            if(\App\Aparato::where('nombre',$ap['ssid'])->count()==0) {
                $a=new \App\Aparato;
                $a->ip=$ap['ip'];
                $a->nombre=$ap['ssid'];
                try {
                    $a->nodo_id=\App\Nodo::where('legacy_nodo_id',$ap['codigo_nodo'])->firstOrFail()->id;
                }catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                    echo "Unkown legacy node id: $ap[codigo_nodo]\n";
                    print_r($ap);
                    continue;
                }
                $a->plataforma=1;
                $a->puerto=443;
                $a->usuario='admin';
                $a->password='Sxc709lP';
                $a->rol=4;
                $a->save();
            }
        }
        
        foreach($datos['ptp'] as $ap) {
            if(\App\Aparato::where('nombre',$ap['ssid'])->count()==0) {
                $a=new \App\Aparato;
                $a->ip=$ap['ip'];
                $a->nombre=$ap['ssid'];
                try {
                    $a->nodo_id=\App\Nodo::where('legacy_nodo_id',$ap['codigo_nodo'])->firstOrFail()->id;
                }catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                    echo "Unkown legacy node id: $ap[codigo_nodo]\n";
                    print_r($ap);
                    continue;
                }
                $a->plataforma=1;
                $a->rol=1;
                $a->puerto=443;
                $a->usuario='admin';
                $a->password='Sxc709lP';
                $a->save();
            }
        }
    }
}
