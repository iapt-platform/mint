<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Tools\PaliSearch;

class TestSearchPali extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan test:search.pali
     * @var string
     */
    protected $signature = 'test:search.pali {word?}';

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
        $word = $this->argument('word');
        if(empty($word)){
            $word = 'citta';
        }
        $words = str_replace('_',' ',$word);
        $words = explode(',',$words);
        $this->info("searching word={$word} limit=10,offset=0");
        $result = PaliSearch::search($words,[],'case',0,10);
        if($result){
            $this->info("word={$word} total=".$result['total']);
        }else{
            $this->error("word={$word} search fail");
        }

        $rpc_result = PaliSearch::book_list($words,
                                            [],
                                            'case');
        $this->info('book list count='.count($rpc_result['rows']));

        $this->info("searching word={$word} limit=10,offset=10");
        $result = PaliSearch::search($words,[],'case',10,10);
        if($result){
            $this->info("word={$word} total=".$result['total']);
        }else{
            $this->error("word={$word} search fail");
        }
        $this->info("searching word={$word} book=267");
        $result = PaliSearch::search($words,[267],'case',0,3);
        if($result){
            $this->info("word={$word} book=267 total=".$result['total']);
        }else{
            $this->error("word={$word} book=267 search fail");
        }

        return 0;
    }
}
