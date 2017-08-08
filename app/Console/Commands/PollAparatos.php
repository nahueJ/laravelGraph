<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Schema;
use DB;

class PollAparatos extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'solucion:pollaparatos';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Recolectar datos de aparatos';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	// FIXME: definir esto en \App\Aparato? no sé dónde se acostumbra definir en Laravel
	const Ubiquiti = 1;
	const Mikrotik = 2;

	// las rutas a los scripts deben ser relativas a app/Scripts/
	protected $platform_tools = [
		self::Ubiquiti => [
			//'script' => 'stub.php'
			'script' => 'ubiquiti.js'
		]
	];
	
	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		$total = 0;
		$tools = [ ];
		$datos = [ ];
		$errores = [ ];
		foreach ([ self::Ubiquiti, self::Mikrotik ] as $plataforma) {
			// verificar que hay un script configurado para esta plataforma
			if (!isset ($this->platform_tools[$plataforma])) {
				continue;
			}
			// traer todos los aparatos de la plataforma en cuestión
			$aparatos = \App\Aparato::where('plataforma',$plataforma)->get()->toArray ();
			if (!count ($aparatos)) {
				continue;
			}
			$input = [ ];
			foreach ($aparatos as $aparato) {
				$datos_aparato = json_encode ([ 'id' => $aparato['id'], 'ip' => $aparato['ip'], 'puerto' => $aparato['puerto'], 'usuario' => $aparato['usuario'], 'password' => $aparato['password'] ]);
				$input[] = $datos_aparato;
				if ($this->option ('verbose')) {
					$this->comment ($datos_aparato);
				}
				$total++;
			}
			$input = implode ("\n", $input)."\n";
			// abrir el script
			$script = $this->platform_tools[$plataforma]['script'];
			$proc = proc_open (app_path()."/Scripts/".$script, [ 0 => [ 'pipe', 'r' ], 1 => [ 'pipe', 'w' ], 2 => [ 'pipe', 'w' ] ], $pipes);
			// enviar al script los aparatos por la entrada estándar del mismo
			fwrite ($pipes[0], $input);
			fclose ($pipes[0]);
			$tool = [
				'script' => $script,
				'proc' => $proc,
				'output' => $pipes[1],
				'errors' => $pipes[2],
				'buffer' => '',
				'done' => false
			];
			stream_set_blocking ($pipes[1], false);
			stream_set_blocking ($pipes[2], false);
			$tools[] = $tool;
		}
		
		$this->info ("Se procesarán ".$total." aparatos");
		if ($this->option ('verbose')) {
			$this->comment ("Se mostrará info detallada durante el proceso");
		}
		$progressBar = $this->output->createProgressBar ($total);
		$progressBar->display ();
		while (true) {
			$outputs = [ ];
			foreach ($tools as $tool) {
				if ($tool['done']) {
					continue;
				}
				$outputs[] = $tool['output'];
				$outputs[] = $tool['errors'];
			}
			if (!count ($outputs)) {
				break;
			}
			$zend_limitation = NULL;	// para una explicación de $zend_limitation véase http://php.net/manual/es/function.stream-select.php#refsect1-function.stream-select-notes
			stream_select ($outputs, $zend_limitation, $zend_limitation, NULL);
			foreach ($tools as $i => $tool) {
				if (in_array ($tool['output'], $outputs)) {
					$buffer = fread ($tool['output'], 65536);
					if (gettype($buffer) != 'string' || strlen ($buffer) == 0) {
						$tools[$i]['done'] = true;
						continue;
					}
					$tools[$i]['buffer'] .= $buffer;
					if (strpos ($tools[$i]['buffer'], "\n") !== false) {
						$lines = explode ("\n", $tools[$i]['buffer']);
						$tools[$i]['buffer'] = array_pop ($lines);
						foreach ($lines as $line) {
							$data = json_decode ($line, true);
							if ($data === NULL) {
								$progressBar->clear ();
								$this->error ('app/Scripts/'.$tool['script'].': '.$line);
								$progressBar->display ();
								continue;
							}
							//echo "\nBUENISIMO TRAJO DATOS DE ".$data["id"]."\n";
							if (!isset ($data["id"])) {
								$progressBar->clear ();
								$this->error ('resultado sin id en app/Scripts/'.$tool['script'].': '.$line);
								$progressBar->display ();
								continue;
							}
							if (!isset ($data["data"]) && !isset ($data["error"])) {
								$progressBar->clear ();
								$this->error ('resultado sin datos en app/Scripts/'.$tool['script'].': '.$line);
								$progressBar->display ();
								continue;
							}
                            if (isset ($data["data"])) {
                                echo "id=".$data['id']." OK\n";
								$datos[] = [
									"aparato_id" => $data["id"],
									"timestamp" => time(),
									"datos" => $data["data"]
								];
							} else {
								$errores[$data["id"]] = $data["error"];
							}
							$progressBar->advance ();
						}
					}
				}
				if (in_array ($tool['errors'], $outputs)) {
					$errors = fread ($tool['errors'], 65536);
					$progressBar->clear ();
					foreach (explode ("\n", trim ($errors)) as $line) {
						$this->error ('app/Scripts/'.$tool['script'].': '.$line);
					}
					$progressBar->display ();
				}
			}
		}
		//$progressBar->finish (); $this->line ("");	// NOTA: finish() no imprime caracter de nueva línea, revisar documentación de Laravel
		$progressBar->clear ();
		$this->info ("Conseguimos datos de ".count($datos)." aparatos");
		if (count ($datos)) {
			DB::connection('mongodb')->collection('polling')->insert ($datos);
		}
		if (count ($errores)) {
			$this->error ("Se encontraron errores al procesar estos aparatos:");
			foreach ($errores as $id => $error) {
				$this->error ("aparato #".$id.": ".$error);
			}
		}
	}
}
