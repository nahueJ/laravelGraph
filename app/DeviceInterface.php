<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Connection;

class DeviceInterface extends Model
{
    protected $guarded = ['aparato_id', 'interface_type', 'OID'];
	
	public function aparato(){
		return $this->belongsTo('\App\Aparato','aparato_id');
	}
	
	public function pairInterface(){
		return $this->hasOne(DeviceInterface::class,'interface_pair_id');
	}

	public function addConnection(){	
		$interfacesSubred = DeviceInterface::where('subred', $this->subred)->whereNotIn('id',[$this->id])->get();	
		
		switch ($interfacesSubred->count()){
			case 0:
				$this->estado = 'inactiva';
				$this->save();
				break;
			case 1:
				$pairInterface = $interfacesSubred->first();
				if($pairInterface->estado == 'inactiva'){
					$pairInterface->estado = 'activa';
					$pairInterface->interface_pair_id = $this->id;
					$pairInterface->save();
					$this->estado = 'activa';
					$this->interface_pair_id = $pairInterface->id;
					$this->save();
					return 1;
				} else if(is_null($pairInterface->estado)){
					$this->estado = 'inactiva';
					$this->save();
				} else {
					$this->estado = 'error';
					$this->save();
					echo "ERROR ".$this->subred;
				}
				break;
			default :
				$this->estado = 'error';
				$this->save();
				echo "\nERROR mas de 2 conexiones para ".$this->subred;
				break;
		}
		return 0;
	}

	public function rmConnection(){
		if(!(is_null($this->interface_pair_id))) {
			//si hay conexion a otra itf
			$this->pairInterface()->first()->desconectar();
			$this->interface_pair_id=NULL;	//elimina la relacion entre las itf
		}
		//pasar el estado a null
		unset($this->estado);
		$this->save();
	}
	
	public function desconectar(){
		$this->estado = 'inactiva';	//pasando estado de la itf par a inactiva
		$this->interface_pair_id=NULL;	//elimina la relacion entre las itf
		$this->save();
		$this->aparato()->first()->nodo()->first()->actualizarJerarquia();
	}
}
