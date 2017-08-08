<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateStaticGraph extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'solucion:generate_graph';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates graph for dashboard';

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
        $ts_now=time();
        $trigger=-55; 

        $lista=[];
        $grafico_general=[];

        foreach (\App\AparatoStat::distinct('aparato_id')->get(['aparato_id']) as $a ) {

            $id=$a->getAttributes();
            $stat=\App\AparatoStat::where('aparato_id',$id['0'])->orderBy('timestamp','desc')->take(1)->first();

            $score=0;
            
            $fecha_poll=$stat->timestamp;

            foreach($stat->client_stats()->get() as $c_stat) {
                if($c_stat->signal<$trigger)
                    $score++;
            }

            $ap=\App\Aparato::findOrFail($id['0']);
            $lista[]=['score'=>$score,'aparato'=>$ap->toArray()];

            $i=86400;
            while($i>0) {                
                $stats=\App\AparatoStat::where('aparato_id',$id['0'])->where('timestamp','>=',($ts_now-$i))
                    ->take(1)
                    ->get();

                $score=0;

                foreach($stats as $stat) {
                    foreach($stat->client_stats()->get() as $c_stat) {
                        if($c_stat->signal<$trigger)
                            $score++;
                    }
                }
                if(isset($grafico_general[$ts_now-$i]))
                    $grafico_general[$ts_now-$i]+=$score;
                else
                    $grafico_general[$ts_now-$i]=$score;
                $i=$i-3600;
            }
        }


        $ccms_total=[];

        foreach($grafico_general as $ts=>$score)
            $ccms_total[]=[$ts*1000,$score];

        $this->sksort($lista,'score');

        $top_ten_ccms=[];

        foreach(array_slice($lista,0,15) as $item) 
            $top_ten_ccms[]=[$item['aparato']['nombre'],$item['score']];


        $sg=\App\StaticGraph::find(1);
        if(!isset($sg->id))
            $sg=new \App\StaticGraph;
        $sg->top_aparatos=json_encode($top_ten_ccms);
        $sg->ccms_graph=json_encode($ccms_total);
        $sg->fecha_poll=$fecha_poll;

        $sg->save();
    }
    private function sksort(&$array, $subkey="id", $sort_ascending=false) {

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
