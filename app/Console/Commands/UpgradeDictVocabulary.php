<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Vocabulary;
use App\Models\UserDict;
use App\Tools\Tools;

class UpgradeDictVocabulary extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan upgrade:dict.vocabulary
     * @var string
     */
    protected $signature = 'upgrade:dict.vocabulary';

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
        if(\App\Tools\Tools::isStop()){
            return 0;
        }
        $words = UserDict::where('source','_PAPER_')->selectRaw('word,count(*)')->groupBy('word')->cursor();

		$bar = $this->output->createProgressBar(230000);
		foreach ($words as $word) {
			$update = Vocabulary::firstOrNew(
                ['word' => $word->word],
                ['word_en'=>Tools::getWordEn($word->word)]
            );
            $update->count = $word->count;
            $update->flag = 1;
            $update->strlen = mb_strlen($word->word,"UTF-8");
            $update->save();
            $bar->advance();
		}
        $bar->finish();
        Vocabulary::where('flag',0)->delete();
        return 0;
    }
}
