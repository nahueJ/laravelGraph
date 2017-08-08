<?php

use Illuminate\Database\Seeder;

class NodoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		DB::table('nodos')->truncate();
		
        App\Nodo::create([
			'nombre' => 'Cerro Arco',
			'coordenadas' => 'aaa',
			'jerarquia' => 0
		]);
		App\Nodo::create([
			'nombre' => 'Geoandina',
			'coordenadas' => 'aaa'
		]);
		App\Nodo::create([
			'nombre' => 'Alto Oeste',
			'coordenadas' => 'aaa'
		]);
		App\Nodo::create([
			'nombre' => 'MixFM',
			'coordenadas' => 'aaa'
		]);
		App\Nodo::create([
			'nombre' => 'Favorita',
			'coordenadas' => 'aaa'
		]);
		App\Nodo::create([
			'nombre' => 'Champagnat',
			'coordenadas' => 'aaa'
		]);
		App\Nodo::create([
			'nombre' => 'Volcan',
			'coordenadas' => 'aaa'
		]);
		App\Nodo::create([
			'nombre' => 'Providencia',
			'coordenadas' => 'aaa'
		]);
		App\Nodo::create([
			'nombre' => 'Compania',
			'coordenadas' => 'aaa'
		]);
		App\Nodo::create([
			'nombre' => 'Challao',
			'coordenadas' => 'aaa'
		]);
    }
}
