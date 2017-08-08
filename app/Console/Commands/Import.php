<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class Import extends Command
{

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'solucion:import {file}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Importa CSV a tabla aparatos';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
     */

	public function handle()
    {
        $fp=fopen($this->argument('file'),"r");

        if($fp==false) {
            $this->error('Unable to open '.$this->argument('file'));
            exit(1);
        }

        $line_count=0;
        $ok_count=0;

        while(($line=fgetcsv($fp,1024))!=false) {
            $line_count++;
            //ded124B05pac,4,10.124.0.58,443,admin,Sxc709lP,1
            
            if(count($line)!=7) {
                $this->error("Line $line_count invalid, skipped");
                continue;
            }

            $a=\App\Aparato::where('nombre',$line[0])->first();
            if(isset($a->id)) {
                $this->error("Line $line_count, aparato named $line[0] already exists, skipped");
                continue;
            }

            $a=new\App\Aparato;
            $a->nombre=$line[0];
            $a->rol=$line[1];
            $a->ip=$line[2];
            $a->puerto=$line[3];
            $a->usuario=$line[4];
            $a->password=$line[5];
            $a->plataforma=$line[6];

            $a->save();

            $ok_count++;

        }

        fclose($fp);

        $this->info("$ok_count records were imported");
        
	}
}
