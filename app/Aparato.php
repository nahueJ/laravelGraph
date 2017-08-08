<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Aparato extends Model
{
    public function hasStats() {
        if(\App\AparatoStat::where('aparato_id',$this->id)->count()>=2)
            return true;
        return false;
    }


    //
}
