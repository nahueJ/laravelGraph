<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Nodo extends Model
{
	protected $guarded = ['nombre', 'coordenadas'];

	public function aparatos() {
		return $this->hasMany('\App\Aparato','nodo_id');
	}
	
	public function aps() {
        return $this->hasMany('\App\Aparato','nodo_id')->where('rol',4);
    }	

	private function propagarMiJerarquia($nodosOrdenados,$nodosJerarquiaNull){
		//de la lista de nodos con los que tengo conexion, elimino los que tienen jeraquia mayor, igual a la mia, o una inferior
//		echo "Eliminando nodos de mayor jerarquia\n";
		if(!(is_null($this->jerarquia))){
			while ($nodosOrdenados->first()->jerarquia <= (1+$this->jerarquia)) {
				$nodosOrdenados->shift();
				if ($nodosOrdenados->isEmpty()){
					break;
				}
			}
		}
		//a los que quedan (que tienen jerarquia mas baja que la mia + 1) les envio la orden de actualizar su jerarquia
		if ($nodosOrdenados->isNotEmpty()){
//			echo "Nodos con jerarquia menor que corregir ".$nodosOrdenados."\n\n";
			foreach($nodosOrdenados as $nodoHijo){
//				echo $this->nombre." enviando orden a ".$nodoHijo->nombre." con jerarquia ".$nodoHijo->jerarquia."\n";
				$nodoHijo->actualizarJerarquia();
			}
		} else {
//			echo "No hay nodos con jerarquia menor que corregir\n";
		}
		//si hay conexion con algun nodo con jerarquia = null, les envio la orden de actualizar su jerarquia
		if ($nodosJerarquiaNull->isNotEmpty()){
//			echo "Nodos con jerarquia null que corregir ".$nodosJerarquiaNull."\n\n";
			foreach($nodosJerarquiaNull as $nodoHijo){
//				echo $this->nombre." enviando orden a ".$nodoHijo->nombre." con jerarquia ".$nodoHijo->jerarquia."\n";
				$nodoHijo->actualizarJerarquia();
			}
		} else {
//			echo "No hay nodos con jerarquia null que corregir\n";
		}
	}

	public function actualizarJerarquia(){
		//echo "\nActualizando Jerarquia ". $this->nombre."\n";
		//recuperar los nodos de todas las conexiones activas (agregar fc en las interf para recuperar nodo)
		$misRouters = $this->aparatos()->where('rol','2')->get();
		$nodosProximos = collect();
		$nodosJerarquiaNull = collect();
		//recupero todos los nodos con los que tengo conexion
		foreach ($misRouters as $router){
			$devItfs = $router->device_interface()->get();
			foreach ($devItfs as $itf) {
				if ($itf->estado == 'activa'){
					$parNodo = $itf->pairInterface()->first()->aparato()->first()->nodo()->first();
					if(is_null($parNodo->jerarquia)){
						$nodosJerarquiaNull->push($parNodo);
					} else {
						$nodosProximos->push($parNodo);
					}
				}
			}
		}

		//echo "nodos null ".$nodosJerarquiaNull."\n";
		//En funcion de las jerarquias del nodo y de los nodos con los que se conecta, se propaga la mayor jerarquia
		if(is_null($this->jerarquia)){
			//Si la jerarquía del nodo que estoy actualizando es NULL
			if ($nodosProximos->isEmpty()){
				//echo "Error: Todas las jerarquias NULL-> Definir nodo root";
			} else {
				//seteo la jerarquia del nodo uno mas bajo que el nodo de mayor jerarquia
				$nodosOrdenados = $nodosProximos->sortBy('jerarquia');
				$this->jerarquia = 1 + $nodosOrdenados->first()->jerarquia;
				$this->save();
				//echo $this->nombre." actualiza a jerarquia:". $this->jerarquia."\n";
				$this->propagarMiJerarquia($nodosOrdenados,$nodosJerarquiaNull);
			}
		} else {
			if ($nodosProximos->isEmpty()){
				//ningun nodo con el que me conecto tiene jerarquia
				if ($nodosJerarquiaNull->isNotEmpty()){
					//si tengo nodos conectados con jerarquia NULL
					if ($this->jerarquia == 0){ //si es nodo raiz propaga su jerarquia
						foreach($nodosJerarquiaNull as $nodoHijo){
							$nodoHijo->actualizarJerarquia();
						}
					} else { //si no es raiz y mi jerarquia es null, mi jerarquia es incorrecta
						$this->jerarquia = NULL;
						$this->save();
						foreach($nodosJerarquiaNull as $nodoHijo){
							$nodoHijo->actualizarJerarquia();
						}
					}
				} else {
					//si tengo jerarquia, y no estoy conectado a nada
					if ($this->jerarquia != 0){ //a menos que sea nodo raiz
						//echo "\n".$this->nombre." quedo fuera de la red\n";
						$this->jerarquia = NULL;
						$this->save();
					}
				}
			} else {
				//seteo la jerarquia del nodo uno mas bajo que el nodo de mayor jerarquia
				$nodosOrdenados = $nodosProximos->sortBy('jerarquia');
				if($this->jerarquia > 1 + $nodosOrdenados->first()->jerarquia){
					//Si estoy conectado con un nodo de jerarquia mayor a la mia (dos saltos o mas) actualizo mi jerarquia para estar a solo un salto
					$this->jerarquia = 1 + $nodosOrdenados->first()->jerarquia;
					$this->save();
					$this->propagarMiJerarquia($nodosOrdenados,$nodosJerarquiaNull);
					//echo $this->nombre." actualiza a jerarquia:". $this->jerarquia."\n";
				} elseif ($this->jerarquia <= $nodosOrdenados->first()->jerarquia) {
					if ($this->jerarquia != 0){ //a menos que sea nodo raiz
						//echo "\n".$this->nombre." quedo fuera de la red\n";
						$this->jerarquia = NULL;
						$this->save();
						$this->propagarMiJerarquia($nodosOrdenados,$nodosJerarquiaNull);
					}
				}
			}
		}
	}

	public function delAparatos(){
		$misAparatos = $this->aparatos()->get();
		foreach ($misAparatos as $a){
			//si es router, borrar itf
			if($a->rol == 2){
				$a->uncharge_interfaces();
			}
			//borra cada aparato
			$a->delete();
		}
	}

	public function getParent(){
		//recuperar los nodos de todas las conexiones activas (agregar fc en las interf para recuperar nodo)
		$misRouters = $this->aparatos()->where('rol','2')->get();
		//recupero todos los nodos con los que tengo conexion
		$parent = NULL;
		foreach ($misRouters as $router){
			$devItfs = $router->device_interface()->get();
			foreach ($devItfs as $itf) {
				if ($itf->estado == 'activa'){
					$parNodo = $itf->pairInterface()->first()->aparato()->first()->nodo()->first();
					//$parent = $parNodo;
					if(!(is_null($parNodo->jerarquia))){
						if($parNodo->jerarquia < $this->jerarquia){
							$parent = $parNodo;
						}
					}
				}
			}
		}
		if (is_null($parent)){
			$parent = new Nodo();
			$parent->nombre = NULL;
		}
		return $parent;
	}

	public function getChilds(){
		//recuperar los nodos de todas las conexiones activas (agregar fc en las interf para recuperar nodo)
		$misRouters = $this->aparatos()->where('rol','2')->get();
		//recupero todos los nodos con los que tengo conexion
		$childs = collect();
		foreach ($misRouters as $router){
			$devItfs = $router->device_interface()->get();
			foreach ($devItfs as $itf) {
				if ($itf->estado == 'activa'){
					$parNodo = $itf->pairInterface()->first()->aparato()->first()->nodo()->first();
					//$parent = $parNodo;
					if(!(is_null($parNodo->jerarquia))){
						if($parNodo->jerarquia > $this->jerarquia){
							$childs->push($parNodo);
						}
					}
				}
			}
		}
		return $childs;
	}

	public function getIP(){
		$router = $this->aparatos()->where('rol','2')->first();
		return $router->ip;
	}

	public function updateConexiones(){
		$ch = $this->getChilds();
		$aux = 0;
		foreach ($ch as $c) {
			$aux = $aux + $c->conexionesPropias + $c->conexionesHeredadas;
		}
		$this->conexionesHeredadas = $aux;
	}
}
