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
        $word = 'citta';
        $result = PaliSearch::search([$word],[],'case',0,3);
        if($result){
            $this->info("word={$word} total=".$result['total']);
        }else{
            $this->error("word={$word} search fail");
        }

        $result = PaliSearch::search([$word],[267],'case',0,3);
        if($result){
            $this->info("word={$word} book=267 total=".$result['total']);
        }else{
            $this->error("word={$word} book=267 search fail");
        }

        return 0;
    }
}
