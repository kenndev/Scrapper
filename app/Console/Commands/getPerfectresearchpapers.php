<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\ArticleController;

class getPerfectresearchpapers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'articles:getperfectresearch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get all perfect research';

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
     * @return int
     */
    public function handle()
    {
        $articleController = new ArticleController();
        $articleController->getPerfectresearchpapers();

        $this->info('Opperation get perfect research successful');
    }
}
