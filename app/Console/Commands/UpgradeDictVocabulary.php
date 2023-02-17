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
     *
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
        $words = UserDict::where('source','_PAPER_')->selectRaw('word,count(*)')->groupBy('word')->cursor();

		$bar = $this->output->createProgressBar(200000);
		foreach ($words as $word) {
			$update = Vocabulary::where('word',$word->word)->updateOrCreate(
                ['count' => $word->count, 'flag' => 1],
                ['word' => $word->word, 'word_en'=>Tools::getWordEn($word->word)]
            );
            $bar->advance();
		}
        $bar->finish();
        return 0;
    }
}
