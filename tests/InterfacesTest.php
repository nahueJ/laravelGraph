<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\DeviceInterface;
use App\Aparato;
use App\Nodo;
use App\User;

use Illuminate\Support\Facades\DB;

class InterfacesTest extends TestCase
{
	use DatabaseTransactions;

    public function test_interfaces_table()
    {
		$user = factory(App\User::class)->create();
		
		$aparato = new Aparato;
		$aparato->nombre = 'Router router1';
		$aparato->rol = 2;
		$aparato->ip = '10.68.0.1';
		$aparato->puerto = 22;
		$aparato->usuario = 'user';
		$aparato->password = 'pass';
		$aparato->plataforma = 1;
		$aparato->save();
		$aparato->charge_self_interfaces();
		
				
        $this->actingAs($user)
				->visit('/peso/interfaces')
				->see($aparato->id);
    }
	
	public function test_carga_automatica_interfaces()
    { 
		$user = factory(App\User::class)->create();
		
		$aparatonombre = "Cerro Arco";
		$aparatorol = 2;
		$aparatonodo_id = 26;
		$aparatoip = "10.68.0.1";
		$aparatopuerto = 22;
		$aparatousuario = "user";
		$aparatopassword = "pass";
		$aparatoplataforma = 1;
		
		$interfs = snmpwalk($aparatoip,'m0n1t0r','iso.3.6.1.2.1.2.2.1.2');
		$nbItfs = count($interfs);
		
		$this->actingAs($user)
				->visit('/aparatos')
				->click('Nuevo Aparato')
				->seePageIs('/aparato_nuevo')
				->type($aparatonombre, 'nombre')
				->type($aparatorol, 'rol')
				->type($aparatoip, 'ip')
				->type($aparatopuerto, 'puerto')
				->type($aparatousuario, 'usuario')
				->type($aparatopassword, 'password')
				->type($aparatoplataforma, 'plataforma')
				->press('create_button')
				->seeInDatabase('aparatos', [
					'nombre' => $aparatonombre,
					'ip' => $aparatoip	
				])
				->seeInDatabase('device_interfaces', [
					'interface_type' => 'ether11',
					'OID' => 'iso.3.6.1.2.1.2.2.1.2.11',
					'ip' => '172.19.68.113',
					'mascara' => '255.255.255.248',
					'subred' => '172.19.68.112/29'
				]);
	}
	
	public function test_conexion_inactiva()
    {
		/************************************************************************/
		/*Cargar a la base de datos test un nodo y un aparato con sus interfaces*/
		/*hacer el test para cuando se agregue el aparato al evento*/
		/************************************************************************/
		
		$user = factory(App\User::class)->create();

		$nodo = new Nodo;
		$nodo->nombre = 'Nodo1';
		$nodo->coordenadas = 'aaaaa';
        $nodo->save();

		$aparato = new Aparato;
		$aparato->nombre = 'Router Cerro Arco';
		$aparato->rol = 2;
		$aparato->ip = '10.68.0.1';
		$aparato->puerto = 22;
		$aparato->usuario = 'user';
		$aparato->password = 'pass';
		$aparato->plataforma = 1;
		$aparato->save();
		$aparato->charge_self_interfaces();
		
		$response = $this->actingAs($user)->action('POST', 'NodosController@agregar_aparato', ['id' => $nodo->id, 'id_aparato' => $aparato->id]);
		
		//tomo una de las interfaces del aparato para verificar que se añadio a lña tabla conexiones
		$devInterface = $aparato->device_interface()->where(DB::raw('masklen(subred)'),'29')->first();
		
		$this->actingAs($user)
			->seeInDatabase('aparatos', [	
				'id' => $aparato->id,
				'nombre' => $aparato->nombre,
				'nodo_id' => $nodo->id		//TEST aparato asignado al nodo nodo_id
			])
			->seeInDatabase('device_interfaces', [	
				'id' => $devInterface->id,
				'subred' => $devInterface->subred,
				'estado' => 'inactiva'
			])
			->notSeeInDatabase('device_interfaces', [	
				'estado' => 'activa'
			])
			->assertEquals(200, $response->status());
		
	}
	
	public function test_conexion_activa()
    {
		/************************************************************************/
		/*Cargar a la base de datos test un nodo y un aparato con sus interfaces*/
		/*hacer el test para cuando se agregue el aparato al evento*/
		/************************************************************************/
		
		$user = factory(App\User::class)->create();

		$n1 = new Nodo;
		$n1->nombre = 'Nodo1';
		$n1->coordenadas = 'aaaaa';
        $n1->save();
		
		$n2 = new Nodo;
		$n2->nombre = 'Nodo2';
		$n2->coordenadas = 'aaaaa';
        $n2->save();

		$ap1 = new Aparato;
		$ap1->nombre = 'Router Cerro Arco';
		$ap1->rol = 2;
		$ap1->ip = '10.68.0.1';
		$ap1->puerto = 22;
		$ap1->usuario = 'user';
		$ap1->password = 'pass';
		$ap1->plataforma = 1;
		$ap1->save();
		$ap1->charge_self_interfaces();
		
		$ap2 = new Aparato;
		$ap2->nombre = 'Router MixFM';
		$ap2->rol = 2;
		$ap2->ip = '10.95.0.1';
		$ap2->puerto = 22;
		$ap2->usuario = 'user';
		$ap2->password = 'pass';
		$ap2->plataforma = 1;
		$ap2->save();
		$ap2->charge_self_interfaces();
		
		$response1 = $this->actingAs($user)->action('POST', 'NodosController@agregar_aparato', ['id' => $n1->id, 'id_aparato' => $ap1->id]);
		$response2 = $this->actingAs($user)->action('POST', 'NodosController@agregar_aparato', ['id' => $n2->id, 'id_aparato' => $ap2->id]);
		
		$this->actingAs($user)
			->seeInDatabase('aparatos', [
				'id' => $ap1->id,
				'nombre' => $ap1->nombre,
				'nodo_id' => $n1->id		//TEST aparato asignado al nodo nodo_id
			])
			->seeInDatabase('aparatos', [
				'id' => $ap2->id,
				'nombre' => $ap2->nombre,
				'nodo_id' => $n2->id		//TEST aparato asignado al nodo nodo_id
			])
			->seeInDatabase('device_interfaces', [
				'estado' => 'activa'
			])
			->assertEquals(200, $response1->status());

		$this->actingAs($user)
			->assertEquals(200, $response2->status());
	}
}
