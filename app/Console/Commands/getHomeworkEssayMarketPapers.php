<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\ArticleController;

class getHomeworkEssayMarketPapers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'articles:getHomeworkEssayMarketPapers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get all Homework Essay Market papers';

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
        $articleController->getHomeworkEssayMarketPapers();

        $this->info('Opperation get Homework Essay Market papers successful');
    }
}
