<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Aparato;
Use Carbon\Carbon;

class PollUbiquiti implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $aparato;
    protected $ts;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Aparato $aparato,$ts)
    {
        $this->aparato=$aparato;
        $this->ts=$ts;
    }

    private function create_aparato_stat() {
        $a=new \App\AparatoStat;           
        $a->aparato_id=$this->aparato->id;
        $a->timestamp=$this->ts;
        $a->save();

        return $a;

    }
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data=$this->fetch_ubiquiti($this->aparato);

        if($data!=null) {
    //        echo $this->aparato->ip.'('.$this->aparato->id.") got data OK\n";
            $data=json_decode($data,true);
            
            if(isset($data['wireless']['sta'])) {

                $a=$this->create_aparato_stat();
               
                foreach($data['wireless']['sta'] as $client_stat) {
                    $s=new \App\ClientStat($client_stat);
                    $a->client_stats()->save($s);
                }

                if($this->aparato->ubiquiti_subtype!=0) {
                    $this->aparato->ubiquiti_subtype=0;
                    $this->aparato->save();
                }

            } else if(isset($data[0]['mac'])) {

                $a=$this->create_aparato_stat();

                foreach($data as $client_stat) {
                    $s=new \App\ClientStat(['mac'=>$client_stat['mac'],'signal'=>$client_stat['signal']]);
                    $a->client_stats()->save($s);
                }
                if($this->aparato->ubiquiti_subtype!=1) {
                    $this->aparato->ubiquiti_subtype=1;
                    $this->aparato->save();
                }
            } else {
                echo $this->aparato->ip.'('.$this->aparato->id.") unsupported data\n";
                print_r($data);
            }
        }
		//ADD...
    }

    private function fetch_ubiquiti($a,$http='s') {

        if($a->puerto==80)
            $http='';

//        echo '['.Carbon::now()->toDateTimeString()."] fetch_ubiquiti(http$http://".$a->ip.") start\n";

        $cookie_file='/tmp/'.$a->id.'.cookies';
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST=>false,
            CURLOPT_COOKIEJAR=> $cookie_file,
            CURLOPT_COOKIEFILE=> $cookie_file,
          CURLOPT_URL => "http$http://".$a->ip."/login.cgi?uri=/status.cgi",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT =>  env('CURL_TIMEOUT'),
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_HTTPHEADER => array(
            "cache-control: no-cache",
          ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo '['.Carbon::now()->toDateTimeString()."] ($a->ip) cURL #1 Error #:" . $err."\n";
            if(strstr($err,"Connection refused")) {
                if($http=='' || $a->puerto==80) {
                    //update aparato type if failed re-test
                    $a->plataforma=2;
                    $a->puerto=22;
                    $a->save();                    
                    return null;
                }
                if($http=='s' || $a->puerto==443) {
                    $a->puerto=80;
                    $a->save();
                    return $this->fetch_ubiquiti($a,'');
                }
            }
            return null;
        } else {
        //  echo $response;
        }

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST=>false,
            CURLOPT_COOKIEJAR=> $cookie_file,
            CURLOPT_COOKIEFILE=> $cookie_file,

          CURLOPT_URL => "http$http://".$a->ip."/login.cgi",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => env('CURL_TIMEOUT'),
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"uri\"\r\n\r\n/status.cgi\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"username\"\r\n\r\n".$a->usuario."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"password\"\r\n\r\n".$a->password."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--",
          CURLOPT_HTTPHEADER => array(
            "cache-control: no-cache",
            "content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW",
          ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo '['.Carbon::now()->toDateTimeString()."] ($a->ip) cURL #2 Error #:" . $err."\n";
            return null;
        } else {
            if(strstr($response,"Invalid credentials")) {
                echo '['.Carbon::now()->toDateTimeString()."] ($a->ip) invalid user or password\n";
                return null;
            }
        }

        if($this->aparato->ubiquiti_subtype==0)
            $curl = curl_init("http$http://".$a->ip."/status.cgi");
        else if($this->aparato->ubiquiti_subtype==1)
            $curl = curl_init("http$http://".$a->ip."/sta.cgi");

        curl_setopt_array($curl, array(
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST=>false,
            CURLOPT_COOKIEJAR=> $cookie_file,
            CURLOPT_COOKIEFILE=> $cookie_file,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT =>  env('CURL_TIMEOUT'),
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_HTTPHEADER => array(
            "cache-control: no-cache",
          ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo '['.Carbon::now()->toDateTimeString()."] ($a->ip) cURL #3 Error #:" . $err."\n";
            return null;
        } else {
            //            echo '['.Carbon::now()->toDateTimeString()."] fetch_ubiquiti(".$a->ip.") end\n";
            //

            return $response;
        }

    }


}
