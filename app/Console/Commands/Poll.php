<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Schema;
use DB;
use Illuminate\Foundation\Bus\DispatchesJobs;

class Poll extends Command
{

    use DispatchesJobs;

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'solucion:poll';

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

	/**
	 * Execute the console command.
	 *
	 * @return mixed
     */

	public function handle()
    {
        $ts=time();
        foreach(\App\Aparato::where('plataforma',1)->where('rol',4)->get() as $aparato)
            $this->dispatch(new \App\Jobs\PollUbiquiti($aparato,$ts));
	}
}
