<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WordIndex;
use App\Models\UserDict;


class UpgradeVocabulary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upgrade:vocabulary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '刷新词汇表完成情况';

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
        if(\App\Tools\Tools::isStop()){
            return 0;
        }
		$words = UserDict::select('word')->groupBy('word')->cursor();
		$count=0;
		foreach ($words as $word) {
			$update = WordIndex::where('word',$word->word)->update(['final'=>1]);
			if($update==1){
				$this->info("{$count}. {$word->word}");
				$count++;
			}
		}
        return 0;
    }
}
