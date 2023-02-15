<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserDict;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class UpgradeDictDefaultMeaning extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upgrade:dict.default.meaning';

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
        # 获取字典中所有的语言
        $langInDict = UserDict::select('language')->groupBy('language')->get();
        $languages = [];
        foreach ($langInDict as $lang) {
            if(!empty($lang["language"])){
                $languages[] = $lang["language"];
            }
        }
		print_r($languages);
        foreach ($languages as $thisLang) {
            Log::info("running $thisLang");
            $bar = $this->output->createProgressBar(UserDict::where('source','_PAPER_')
                                                        ->where('language',$thisLang)->count());
            foreach (UserDict::where('source','_PAPER_')
                                ->where('language',$thisLang)
                                ->select('word','note')
                                ->cursor() as $word) {
                Cache::put("dict_first_mean/{$thisLang}/{$word['word']}", mb_substr($word['note'],0,50,"UTF-8") ,24*3600);
                $bar->advance();
            }
            $bar->finish();
        }
        Log::info("running com");
        $bar = $this->output->createProgressBar(UserDict::where('source','_PAPER_')->count());
        foreach (UserDict::where('source','_PAPER_')
                            ->select('word','note')
                            ->cursor() as $word) {
            $key = "dict_first_mean/com/{$word['word']}";
            Cache::put($key, mb_substr($word['note'],0,50,"UTF-8") ,24*3600);
            $bar->advance();
        }
        $bar->finish();

        return 0;
    }
}
