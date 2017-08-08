<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\DeviceInterface;
use App\Aparato;
use App\Nodo;
use App\User;

class ParentescoTest extends TestCase
{
	use DatabaseTransactions;
	
	public function test_jerarquias_null_error()
    {
		$user = factory(App\User::class)->create();
		
		$n1 = new Nodo;
		$n1->nombre = 'Portal';
		$n1->coordenadas = 'aaaaa';
        $n1->save();
		
		$n2 = new Nodo;
		$n2->nombre = 'Olavarria';
		$n2->coordenadas = 'aaaaa';
        $n2->save();
		
		$n3 = new Nodo;
		$n3->nombre = 'Perdriel';
		$n3->coordenadas = 'aaaaa';
        $n3->save();
		
		$ap1 = new Aparato;
		$ap1->nombre = 'Router Portal';
		$ap1->rol = 2;
		$ap1->ip = '10.79.0.1';
		$ap1->puerto = 22;
		$ap1->usuario = 'user';
		$ap1->password = 'pass';
		$ap1->plataforma = 1;
		$ap1->save();
		$ap1->charge_self_interfaces();
		
		$ap2 = new Aparato;
		$ap2->nombre = 'Router Olavarria';
		$ap2->rol = 2;
		$ap2->ip = '10.116.0.1';
		$ap2->puerto = 22;
		$ap2->usuario = 'user';
		$ap2->password = 'pass';
		$ap2->plataforma = 1;
		$ap2->save();
		$ap2->charge_self_interfaces();
		
		$ap3 = new Aparato;
		$ap3->nombre = 'Router Perdriel';
		$ap3->rol = 2;
		$ap3->ip = '10.63.0.1';
		$ap3->puerto = 22;
		$ap3->usuario = 'user';
		$ap3->password = 'pass';
		$ap3->plataforma = 1;
		$ap3->save();
		$ap3->charge_self_interfaces();
		
		$response1 = $this->actingAs($user)->action('POST', 'NodosController@agregar_aparato', ['id' => $n1->id, 'id_aparato' => $ap1->id]);
		$response2 = $this->actingAs($user)->action('POST', 'NodosController@agregar_aparato', ['id' => $n2->id, 'id_aparato' => $ap2->id]);
		$response3 = $this->actingAs($user)->action('POST', 'NodosController@agregar_aparato', ['id' => $n3->id, 'id_aparato' => $ap3->id]);
		
		$this->actingAs($user)
			->assertEquals(200, $response1->status());
		$this->actingAs($user)
			->assertEquals(200, $response2->status());
		$this->actingAs($user)
			->assertEquals(200, $response3->status());
	}
	
	public function test_actualizar_jerarquia()
    {
		$user = factory(App\User::class)->create();
		
		$n1 = new Nodo;
		$n1->nombre = 'Portal';
		$n1->coordenadas = 'aaaaa';
		$n1->save();
		
		$n4 = new Nodo;
		$n4->nombre = 'Perdriel';
		$n4->coordenadas = 'aaaaa';
		$n4->jerarquia = 0;
        $n4->save();
		
		$ap1 = new Aparato;
		$ap1->nombre = 'Router Portal';
		$ap1->rol = 2;
		$ap1->ip = '10.79.0.1';
		$ap1->puerto = 22;
		$ap1->usuario = 'user';
		$ap1->password = 'pass';
		$ap1->plataforma = 1;
		$ap1->save();
		$ap1->charge_self_interfaces();
				
		$ap4 = new Aparato;
		$ap4->nombre = "Router Perdriel";
		$ap4->rol = 2;
		$ap4->puerto = 26;
		$ap4->ip = "10.63.0.1";
		$ap4->puerto = 22;
		$ap4->usuario = "user";
		$ap4->password = "pass";
		$ap4->plataforma = 1;
		$ap4->save();
		$ap4->charge_self_interfaces();
		
		$response4 = $this->actingAs($user)->action('POST', 'NodosController@agregar_aparato', ['id' => $n4->id, 'id_aparato' => $ap4->id]);
		$response1 = $this->actingAs($user)->action('POST', 'NodosController@agregar_aparato', ['id' => $n1->id, 'id_aparato' => $ap1->id]);
		
		$nodosTest = Nodo::all()->sortBy('jerarquia');
		foreach($nodosTest as $nodo){
			echo $nodo."\n";
		}
		
		$this->actingAs($user)
			->seeInDatabase('nodos', [	
				'id' => $n1->id,
				'jerarquia' => (1+$n4->jerarquia)
			])
			->assertEquals(200, $response1->status());
		$this->actingAs($user)
			->assertEquals(200, $response4->status());
	}
	
	public function test_actualizar_jerarquia_uno()
    {
		$user = factory(App\User::class)->create();
		
		$n1 = new Nodo;
		$n1->nombre = 'Portal';
		$n1->coordenadas = 'aaaaa';
		$n1->jerarquia = 0;
        $n1->save();
		
		$n2 = new Nodo;
		$n2->nombre = 'Olavarria';
		$n2->coordenadas = 'aaaaa';
		$n2->jerarquia = 1;
        $n2->save();
		
		$n3 = new Nodo;
		$n3->nombre = 'Lulunta';
		$n3->coordenadas = 'aaaaa';
        $n3->save();
		
		$n4 = new Nodo;
		$n4->nombre = 'Perdriel';
		$n4->coordenadas = 'aaaaa';
        $n4->save();
		
		$ap1 = new Aparato;
		$ap1->nombre = 'Router Portal';
		$ap1->rol = 2;
		$ap1->ip = '10.79.0.1';
		$ap1->puerto = 22;
		$ap1->usuario = 'user';
		$ap1->password = 'pass';
		$ap1->plataforma = 1;
		$ap1->save();
		$ap1->charge_self_interfaces();
		
		$ap2 = new Aparato;
		$ap2->nombre = 'Router Olavarria';
		$ap2->rol = 2;
		$ap2->ip = '10.116.0.1';
		$ap2->puerto = 22;
		$ap2->usuario = 'user';
		$ap2->password = 'pass';
		$ap2->plataforma = 1;
		$ap2->save();
		$ap2->charge_self_interfaces();
		
		$ap3 = new Aparato;
		$ap3->nombre = 'Router Lulunta';
		$ap3->rol = 2;
		$ap3->ip = '10.115.0.1';
		$ap3->puerto = 22;
		$ap3->usuario = 'user';
		$ap3->password = 'pass';
		$ap3->plataforma = 1;
		$ap3->save();
		$ap3->charge_self_interfaces();
		
		$ap4 = new Aparato;
		$ap4->nombre = "Router Perdriel";
		$ap4->rol = 2;
		$ap4->puerto = 26;
		$ap4->ip = "10.63.0.1";
		$ap4->puerto = 22;
		$ap4->usuario = "user";
		$ap4->password = "pass";
		$ap4->plataforma = 1;
		$ap4->save();
		$ap4->charge_self_interfaces();

		$response1 = $this->actingAs($user)->action('POST', 'NodosController@agregar_aparato', ['id' => $n1->id, 'id_aparato' => $ap1->id]);		
		$response2 = $this->actingAs($user)->action('POST', 'NodosController@agregar_aparato', ['id' => $n2->id, 'id_aparato' => $ap2->id]);
		$response3 = $this->actingAs($user)->action('POST', 'NodosController@agregar_aparato', ['id' => $n3->id, 'id_aparato' => $ap3->id]);
		$response4 = $this->actingAs($user)->action('POST', 'NodosController@agregar_aparato', ['id' => $n4->id, 'id_aparato' => $ap4->id]);
		
		$nodosTest = Nodo::all()->sortBy('jerarquia');
		foreach($nodosTest as $nodo){
			echo $nodo."\n";
		}
		
		$this->actingAs($user)
			->assertEquals(200, $response1->status());
		$this->actingAs($user)
			->assertEquals(200, $response2->status());
		$this->actingAs($user)
			->assertEquals(200, $response3->status());
		$this->actingAs($user)
			->assertEquals(200, $response4->status());
	}	
	
	public function test_actualizar_jerarquia_dos()
    {
		$user = factory(App\User::class)->create();
		
		$n1 = new Nodo;
		$n1->nombre = 'Portal';
		$n1->coordenadas = 'aaaaa';
		$n1->jerarquia = 0;
        $n1->save();
		
		$n2 = new Nodo;
		$n2->nombre = 'Olavarria';
		$n2->coordenadas = 'aaaaa';
		$n2->jerarquia = 3;
        $n2->save();
		
		$n3 = new Nodo;
		$n3->nombre = 'Lulunta';
		$n3->coordenadas = 'aaaaa';
        $n3->save();
		
		$n4 = new Nodo;
		$n4->nombre = 'Perdriel';
		$n4->coordenadas = 'aaaaa';
        $n4->save();
		
		$ap1 = new Aparato;
		$ap1->nombre = 'Router Portal';
		$ap1->rol = 2;
		$ap1->ip = '10.79.0.1';
		$ap1->puerto = 22;
		$ap1->usuario = 'user';
		$ap1->password = 'pass';
		$ap1->plataforma = 1;
		$ap1->save();
		$ap1->charge_self_interfaces();
		
		$ap2 = new Aparato;
		$ap2->nombre = 'Router Olavarria';
		$ap2->rol = 2;
		$ap2->ip = '10.116.0.1';
		$ap2->puerto = 22;
		$ap2->usuario = 'user';
		$ap2->password = 'pass';
		$ap2->plataforma = 1;
		$ap2->save();
		$ap2->charge_self_interfaces();
		
		$ap3 = new Aparato;
		$ap3->nombre = 'Router Lulunta';
		$ap3->rol = 2;
		$ap3->ip = '10.115.0.1';
		$ap3->puerto = 22;
		$ap3->usuario = 'user';
		$ap3->password = 'pass';
		$ap3->plataforma = 1;
		$ap3->save();
		$ap3->charge_self_interfaces();
		
		$ap4 = new Aparato;
		$ap4->nombre = "Router Perdriel";
		$ap4->rol = 2;
		$ap4->puerto = 26;
		$ap4->ip = "10.63.0.1";
		$ap4->puerto = 22;
		$ap4->usuario = "user";
		$ap4->password = "pass";
		$ap4->plataforma = 1;
		$ap4->save();
		$ap4->charge_self_interfaces();

		$response1 = $this->actingAs($user)->action('POST', 'NodosController@agregar_aparato', ['id' => $n1->id, 'id_aparato' => $ap1->id]);		
		$response2 = $this->actingAs($user)->action('POST', 'NodosController@agregar_aparato', ['id' => $n2->id, 'id_aparato' => $ap2->id]);
		$response3 = $this->actingAs($user)->action('POST', 'NodosController@agregar_aparato', ['id' => $n3->id, 'id_aparato' => $ap3->id]);
		$response4 = $this->actingAs($user)->action('POST', 'NodosController@agregar_aparato', ['id' => $n4->id, 'id_aparato' => $ap4->id]);
		
		$nodosTest = Nodo::all()->sortBy('jerarquia');
		foreach($nodosTest as $nodo){
			echo $nodo."\n";
		}
		
		$this->actingAs($user)
			->assertEquals(200, $response1->status());
		$this->actingAs($user)
			->assertEquals(200, $response2->status());
		$this->actingAs($user)
			->assertEquals(200, $response3->status());
		$this->actingAs($user)
			->assertEquals(200, $response4->status());
	}	
}
