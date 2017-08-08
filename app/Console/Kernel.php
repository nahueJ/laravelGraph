<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
        Commands\Poll::class,
        Commands\Import::class,
        Commands\GenerateStaticGraph::class,
        Commands\Flush::class,
        Commands\ImportMesa::class,
		Commands\CalcularPeso::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
        //$schedule->command ('solucion:pollaparatos')->withoutOverlapping()->everyFiveMinutes();
        $schedule->command ('solucion:poll')->withoutOverlapping()->everyMinute();
        $schedule->command ('solucion:generate_graph')->withoutOverlapping()->hourly();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
