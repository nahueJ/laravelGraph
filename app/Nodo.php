<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Nodo extends Model
{
    public function aparatos() {
        return $this->hasMany('\App\Aparato','nodo_id');
    }

    public function aps() {
        return $this->hasMany('\App\Aparato','nodo_id')->where('rol',4);
    }
}
