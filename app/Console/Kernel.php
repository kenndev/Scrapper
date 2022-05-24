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
        Commands\GetArticles::class,
    ];
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('articles:get')
            ->everyFifteenMinutes(1)->withoutOverlapping();
        $schedule->command('articles:getEmergencyEssayPapers')
            ->everyTenMinutes(1)->withoutOverlapping();
        // $schedule->command('articles:getHomeworkcraftPapers')
        //     ->everyTenMinutes(2)->withoutOverlapping();
        $schedule->command('articles:getHomeworkEssayMarketPapers')
            ->everyTenMinutes()->withoutOverlapping();
        // $schedule->command('articles:getnerd')
        //     ->everyFifteenMinutes()->withoutOverlapping();
        $schedule->command('articles:getcustomessays')
            ->everyFifteenMinutes(2)->withoutOverlapping();
        $schedule->command('articles:getelitecustomwritings')
            ->everyFifteenMinutes(3)->withoutOverlapping();
        $schedule->command('articles:getperfectresearch')
            ->everyFifteenMinutes()->withoutOverlapping();
        $schedule->command('articles:getskilledpapers')
            ->everyTenMinutes(2)->withoutOverlapping();
        $schedule->command('articles:getEssayWriterPapers')
            ->everyTenMinutes()->withoutOverlapping();
        // $schedule->command('articles:getwritetasks')
        //     ->everyFifteenMinutes(1)->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
