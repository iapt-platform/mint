<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Tools\PaliSearch;

class TestSearchPali extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:search.pali';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $result = PaliSearch::search(['citta'],2,0,10);
        var_dump($result);
        return 0;
    }
}
