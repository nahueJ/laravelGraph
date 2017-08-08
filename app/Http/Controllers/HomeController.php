<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($trigger=-55)
    {
        $ccms_total=[];
        $top_ten_ccms=[];
        $graph_date='---';
        $fecha_poll=0;

        $sg=\App\StaticGraph::find(1);
        if(isset($sg->id)) {
            $ccms_total=json_decode($sg->ccms_graph);
            $top_ten_ccms=json_decode($sg->top_aparatos);
            $graph_date=$sg->updated_at;
            $fecha_poll=$sg->fecha_poll;
        }

        $nodos=[];
        $nodos_list=[];

        foreach(\App\Nodo::with('aps')->has('aps')->get() as $nodo) {

            $score_nodo=0;
            $aparatos=[];

            foreach($nodo->aparatos as $ap) {
                $score_aparato=0;

                $stats=\App\AparatoStat::where('aparato_id',$ap->id)->orderBy('timestamp','desc')
    //                ->take(1)
                    ->first();
                if($stats==null)
                    continue;
//                echo "<pre>";var_dump($stats);die;
                foreach($stats->client_stats()->get()->toArray() as $c_stat) {
//                    echo "<pre>";
  //                  print_r($c_stat);die;
                    if($c_stat['signal']<-55)
                        $score_aparato++;
                    }
                
                $aparatos[]=[$ap->nombre,$score_aparato];
                $score_nodo+=$score_aparato;
            }

            $nodos[]=['nombre'=>$nodo->nombre,'score'=>$score_nodo,'id'=>$nodo->id,'aparatos'=>$aparatos];
        }

        $this->sksort($nodos,'score');
        $nodos=array_slice($nodos,0,10);


        foreach($nodos as $n) {
            $nodos_list[]=\App\Nodo::with('aps')->findOrFail($n['id']);
        }
//        echo "<pre>";
//        print_r($nodos_list);die;

        return view('home',[
            'aparatos_cms'=>json_encode($top_ten_ccms), 
            'aparatos_consultados'=>642,
            /*'aparatos_consultados'=>\App\AparatoStat::raw(function($collection) {
                                        return count($collection->distinct('aparato_id'));
            }),*/
            'total_aparatos'=>\App\Aparato::where('rol',4)->count(),            
            'ccms_graph'=>json_encode($ccms_total),
            'fecha_poll'=>($fecha_poll*1000),
            'trigger'=>$trigger,
            'nodos'=>$nodos_list,
            /*'nodos'=>\App\Nodo::whereHas('aparatos',function($q) {
                $q->where('rol',4);
            })->with('aparatos')->get(),*/
            'nodos_graph'=>$nodos,
        ]

        );
    }

    private function ccms_graph($stats,$trigger) {
        $signal=[];

        foreach($stats as $stat) {
            $ts=$stat->timestamp;

            $score=0;
            foreach($stat->client_stats()->get() as $c_stat) {
                if($c_stat->signal<$trigger)
                    $score++;
            }

            $signal[]=[$stat->timestamp*1000,$score];
        }
        
        return $signal;

    }
    public function stats($id) {
        $stats=\App\AparatoStat::where('aparato_id',(int)$id)
            ->where('timestamp','>',(time()-18000))
            ->get();        

        return response()->json([
            'signal'=>$this->ccms_graph($stats,env('SIGNAL_TRIGGER_VALUE')),
            ]
        );
    } 


    public function generate_stats() {
        //submit job for graph generation
        //store it in redis
    }

    public function sksort(&$array, $subkey="id", $sort_ascending=false) {

        $temp_array=[];

        if (count($array))
            $temp_array[key($array)] = array_shift($array);

        foreach($array as $key => $val){
            $offset = 0;
            $found = false;
            foreach($temp_array as $tmp_key => $tmp_val)
            {
                if(!$found and strtolower($val[$subkey]) > strtolower($tmp_val[$subkey]))
                {
                    $temp_array = array_merge(    (array)array_slice($temp_array,0,$offset),
                                                array($key => $val),
                                                array_slice($temp_array,$offset)
                                              );
                    $found = true;
                }
                $offset++;
            }
            if(!$found) $temp_array = array_merge($temp_array, array($key => $val));
        }

        if ($sort_ascending) $array = array_reverse($temp_array);
        else $array = $temp_array;

    }

}
