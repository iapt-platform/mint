<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\UserDict;
use Illuminate\Support\Facades\DB;

class CacheDictPreference extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:dict.preference';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '从第三方字典中提取首选项';

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
        $prefix = 'dict-preference';
        $words = UserDict::select(['word','language'])
                       ->groupBy(['word','language'])
                       ->cursor();
        $count = DB::select('SELECT count(*) from (
                     SELECT word,language from user_dicts group by word,language) T');
        $bar = $this->output->createProgressBar($count[0]->count);
        foreach ($words as $key => $word) {
            $meaning = UserDict::where('word',$word->word)
                ->where('language',$word->language)
                ->where('source','_PAPER_RICH_')
                ->whereNotNull('mean')
                ->value('mean');
            $meaning = trim($meaning," $");
            if(!empty($meaning)){
                $m = explode('$',$meaning);
                Cache::put("{$prefix}/{$word->word}/{$word->language}",$m[0]);
            }
            $bar->advance();
        }
        $bar->finish();

        return 0;
    }
}
