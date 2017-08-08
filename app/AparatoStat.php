<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class AparatoStat extends Eloquent
{
    protected $connection = 'mongodb';

    public $timestamps=false;
    public function client_stats() {
        return $this->embedsMany('\App\ClientStat');
    }
    //
}
