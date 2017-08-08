<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Aparato extends Model
{
	protected $guarded = [];
	
    public function hasStats() {
        if(\App\AparatoStat::where('aparato_id',$this->id)->count()>=2)
            return true;
        return false;
    }

	public function nodo()
	{
		return $this->belongsTo(Nodo::class);
	}
	
	public function device_interface()
	{
		return $this->hasMany(DeviceInterface::class);
	}
	
	// this is a recommended way to declare event handlers
    protected static function boot() {
        parent::boot();

        static::deleting(function($aparato) { // before delete() method call this
             $aparato->device_interface()->delete();
             // do the rest of the cleanup...
        });
    }
	
	/********************************************************************************/
	/*	Tal vez se debería diferenciar los aparatos en vez de por el atributo rol	*/
	/*	en clases heredadas que implementen un metodo auto set up, que cada aparato	*/
	/*	implementa de manera diferente,para el router seria cargar sus interfaces	*/
	/*	para otros aparatos todavía no se que deberían implementar diferente		*/
	/********************************************************************************/
	public function  charge_self_interfaces()	
	{
		$interfacesIps = snmpwalk($this->ip,'m0n1t0r','iso.3.6.1.2.1.4.20.1.1');
		if(count($interfacesIps) >= 1){
			$interfacesIndices = snmpwalk($this->ip,'m0n1t0r','iso.3.6.1.2.1.4.20.1.2');
			$interfacesMascaras = snmpwalk($this->ip,'m0n1t0r','iso.3.6.1.2.1.4.20.1.3');
			$interfacesNombres = snmprealwalk($this->ip,'m0n1t0r','iso.3.6.1.2.1.2.2.1.2');

			for ($i = 0; $i < count($interfacesIps); $i++) {
				$devInterface=new DeviceInterface;
				/*APARATO ID*/
				$devInterface->aparato_id = $this->id;
				/*MASCARA SUBRED DE INTERFAZ*/
				$auxstring = $interfacesMascaras[$i];
				$auxstring = str_replace("IpAddress: ", "", $auxstring);
				$auxstring = str_replace('"', "", $auxstring);
				$devInterface->mascara = $auxstring;
				/*IP SUBRED DE INTERFAZ*/
				$auxstring = $interfacesIps[$i];
				$auxstring = str_replace("IpAddress: ", "", $auxstring);
				$auxstring = str_replace('"', "", $auxstring);
				$devInterface->ip = $auxstring;
				/*OID INTERFAZ*/
				$auxstring = $interfacesIndices[$i];
				$auxstring = str_replace("INTEGER: ", "", $auxstring);
				$auxstring = str_replace('"', "", $auxstring);
				$devInterface->OID = 'iso.3.6.1.2.1.2.2.1.2.'.$auxstring;
				/*IP Subred*/
				$auxip=ip2long($devInterface->ip);
				$auxmask=ip2long($devInterface->mascara);
				$auxsubnet=$auxip & $auxmask;
				$count = 0;
				while($auxmask)
				{
					$count += ($auxmask & 1);
					$auxmask = $auxmask >> 1;
				}
				$devInterface->subred = long2ip($auxsubnet)."/".$count;
				/*TIPO DE INTERFAZ*/
				$auxstring = $interfacesNombres[$devInterface->OID];
				$auxstring = str_replace("STRING: ", "", $auxstring);
				$auxstring = str_replace('"', "", $auxstring);
				$devInterface->interface_type = $auxstring;
				
				//$devInterface->estado = 'NULL';

				/*$devInterface->interface_pair_id = ;
				$devInterface->estado_negociacion = ;
				$devInterface->link_downs;
				$devInterface->full_duplex;
				*/

				/*GUARDAR*/
				$devInterface->save();

			}
		}
	}
	
	public function conectarInterfaces() {
		//buscar las interfaces del aparato, filtradas con subred /29
		$devInterfaces = $this->device_interface()->where(DB::raw('masklen(subred)'),'29')->get();
		$flag = 0;		
		foreach($devInterfaces as $itf){
			if($itf->addConnection()){
				$flag = 1;
			}
		}
		return $flag;
	}
	
	public function desconectarInterfaces(){
		//pasar el estado a null, pasando las itf par a inactiva
		$itfs = $this->device_interface()->get();
		foreach ($itfs as $itf){
			$itf->rmConnection();
		}
	}
	
	public function uncharge_interfaces(){
		//borrar las itf dejando los pares en inactivo
		$this->desconectarInterfaces();
		$itfs = $this->device_interface()->get();
		//borrarlas
		foreach ($itfs as $itf){
			$itf->delete();
		}
	}
}
