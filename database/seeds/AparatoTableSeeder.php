<?php

use Illuminate\Database\Seeder;

class AparatoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		DB::statement('TRUNCATE aparatos CASCADE');

		$a1 = App\Aparato::create([
			'nombre' => 'Router Cerro Arco',
			'rol' => 2,
			'ip' => '10.68.0.1',
			'puerto' => 22,
			'usuario' => 'admin',
			'password' => 'pass',
			'plataforma' => 1
		]);
		$a1->charge_self_interfaces();
		$a2 = App\Aparato::create([
			'nombre' => 'Router Geoandina',
			'rol' => 2,
			'ip' => '10.107.0.1',
			'puerto' => 22,
			'usuario' => 'admin',
			'password' => 'pass',
			'plataforma' => 1
		]);
		$a2->charge_self_interfaces();
		$a3 = App\Aparato::create([
			'nombre' => 'Router Alto Oeste',
			'rol' => 2,
			'ip' => '10.8.0.1',
			'puerto' => 22,
			'usuario' => 'admin',
			'password' => 'pass',
			'plataforma' => 1
		]);
		$a3->charge_self_interfaces();
		$a4 = App\Aparato::create([
			'nombre' => 'Router MixFM',
			'rol' => 2,
			'ip' => '10.95.0.1',
			'puerto' => 22,
			'usuario' => 'admin',
			'password' => 'pass',
			'plataforma' => 1
		]);
		$a4->charge_self_interfaces();
		$a5 = App\Aparato::create([
			'nombre' => 'Router Favorita',
			'rol' => 2,
			'ip' => '10.22.0.1',
			'puerto' => 22,
			'usuario' => 'admin',
			'password' => 'pass',
			'plataforma' => 1
		]);
		$a5->charge_self_interfaces();
		$a6 = App\Aparato::create([
			'nombre' => 'Router Champagnat',
			'rol' => 2,
			'ip' => '10.14.0.1',
			'puerto' => 22,
			'usuario' => 'admin',
			'password' => 'pass',
			'plataforma' => 1
		]);
		$a6->charge_self_interfaces();
		$a7 = App\Aparato::create([
			'nombre' => 'Router Volcan',
			'rol' => 2,
			'ip' => '10.114.0.1',
			'puerto' => 22,
			'usuario' => 'admin',
			'password' => 'pass',
			'plataforma' => 1
		]);
		$a7->charge_self_interfaces();
		$a8 = App\Aparato::create([
			'nombre' => 'Router Providencia',
			'rol' => 2,
			'ip' => '10.113.0.1',
			'puerto' => 22,
			'usuario' => 'admin',
			'password' => 'pass',
			'plataforma' => 1
		]);
		$a8->charge_self_interfaces();
		$a9 = App\Aparato::create([
			'nombre' => 'Router Compania',
			'rol' => 2,
			'ip' => '10.120.0.1',
			'puerto' => 22,
			'usuario' => 'admin',
			'password' => 'pass',
			'plataforma' => 1
		]);
		$a9->charge_self_interfaces();
		$a10 = App\Aparato::create([
			'nombre' => 'Router Challao',
			'rol' => 2,
			'ip' => '10.13.0.1',
			'puerto' => 22,
			'usuario' => 'admin',
			'password' => 'pass',
			'plataforma' => 1
		]);
		$a10->charge_self_interfaces();
    }
}
