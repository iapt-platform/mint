<?php
/**
 * 刷新字典单词的默认意思
 * 目标：
 *    可以查询到某个单词某种语言的首选意思
 * 算法：
 * 1. 某种语言会有多个字典。按照字典重要程度人工排序
 * 2. 按照顺序搜索这些字典。找到第一个意思就停止。
 */
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserDict;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Tools\RedisClusters;

class UpgradeDictDefaultMeaning extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upgrade:dict.default.meaning {word?}';
    protected $dict = [
        "zh-Hans"=>[
            "8833de18-0978-434c-b281-a2e7387f69be",	/*巴汉字典明法尊者修订版*/
            "f364d3dc-b611-471b-9a4f-531286b8c2c3",	/*《巴汉词典》Mahāñāṇo Bhikkhu编著*/
            "0e4dc5c8-a228-4693-92ba-7d42918d8a91",	/*汉译パーリ语辞典-黃秉榮*/
            "6aa9ec8b-bba4-4bcd-abd2-9eae015bad2b",	/*汉译パーリ语辞典-李瑩*/
            "eb99f8b4-c3e5-43af-9102-6a93fcb97db6",	/*パーリ语辞典--勘误表*/
            "0d79e8e8-1430-4c99-a0f1-b74f2b4b26d8",	/*《巴汉词典》增订*/
        ],
        "zh-Hant"=>[
            "3acf0c0f-59a7-4d25-a3d9-bf394a266ebd",	/*汉译パーリ语辞典-黃秉榮*/
            "5293ffb9-887e-4cf2-af78-48bf52a85304",	/*巴利詞根*/
        ],
        "jp"=>[
            "91d3ec93-3811-4973-8d84-ced99179a0aa",	/*パーリ语辞典*/
            "6d6c6812-75e7-457d-874f-5b049ad4b6de",	/*パーリ语辞典-增补*/
        ],
        "en"=>[
            "c6e70507-4a14-4687-8b70-2d0c7eb0cf21",	/*	Concise P-E Dict*/
            "eae9fd6f-7bac-4940-b80d-ad6cd6f433bf",	/*	Concise P-E Dict*/
            "2f93d0fe-3d68-46ee-a80b-11fa445a29c6",	/*	unity*/
            "b9163baf-2bca-41a5-a936-5a0834af3945",	/*	Pali-Dict Vri*/
            "b089de57-f146-4095-b886-057863728c43",	/*	Buddhist Dictionary*/
            "6afb8c05-5cbe-422e-b691-0d4507450cb7",	/*	PTS P-E dictionary*/
            "0bfd87ec-f3ac-49a2-985e-28388779078d",	/*	Pali Proper Names Dict*/
            "1cdc29e0-6783-4241-8784-5430b465b79c",	/*	Pāḷi Root In Saddanīti*/
            "5718cbcf-684c-44d4-bbf2-4fa12f2588a4",	/*	Critical Pāli Dictionary*/
        ],
        "my"=>[
            "e740ef40-26d7-416e-96c2-925d6650ac6b",	/*	Tipiṭaka Pāḷi-Myanmar*/
            "beb45062-7c20-4047-bcd4-1f636ba443d1",	/*	U Hau Sein’s Pāḷi-Myanmar Dictionary*/
            "1e299ccb-4fc4-487d-8d72-08f63d84c809",	/*	Pali Roots Dictionary*/
            "6f9caea1-17fa-41f1-92e5-bd8e6e70e1d7",	/*	U Hau Sein’s Pāḷi-Myanmar*/
        ],
        "vi"=>[
            "23f67523-fa03-48d9-9dda-ede80d578dd2",	/*	Pali Viet Dictionary*/
            "4ac8a0d5-9c6f-4b9f-983d-84288d47f993",	/*	Pali Viet Abhi-Terms*/
            "7c7ee287-35ba-4cf3-b87b-30f1fa6e57c9",	/*	Pali Viet Vinaya Terms*/
        ],
        "cm"=>[],
    ];
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '找出单词的首选意思。用于搜索列表';

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
        $_word = $this->argument('word');
        # 获取字典中所有的语言
        $langInDict = UserDict::select('language')->groupBy('language')->get();
        $languages = [];
        foreach ($langInDict as $lang) {
            if(!empty($lang["language"])){
                $languages[] = $lang["language"];
            }
        }
		//print_r($languages);
        foreach ($this->dict as $thisLang=>$dictId) {
            $this->info("running $thisLang");

            $bar = $this->output->createProgressBar(UserDict::where('source','_PAPER_')
                                                        ->where('language',$thisLang)->count());
            foreach (UserDict::where('source','_PAPER_')
                                ->where('language',$thisLang)
                                ->select('word','note')
                                ->cursor() as $word) {
                if(!empty($word['note'])){
                    RedisClusters::put("dict_first_mean/{$thisLang}/{$word['word']}", mb_substr($word['note'],0,50,"UTF-8"));
                }
                $bar->advance();
            }
            $bar->finish();

            for ($i=count($dictId)-1; $i >=0 ; $i--) {
                # code...
                $this->info("running $thisLang - {$dictId[$i]}");
                $count = 0;
                foreach (UserDict::where('dict_id',$dictId[$i])
                    ->select('word','note')
                    ->cursor() as $word) {
                        $cacheKey = "dict_first_mean/{$thisLang}/{$word['word']}";
                        if(!empty($word['note'])){
                            $cacheValue = mb_substr($word['note'],0,50,"UTF-8");
                            if(!empty($_word) && $word['word'] === $_word ){
                                Log::info($cacheKey.':'.$cacheValue);
                            }
                            RedisClusters::put($cacheKey, $cacheValue);
                        }

                        if($count % 1000 === 0){
                            $this->info("{$count}");
                        }
                        $count++;
                    }
            }
        }

        return 0;
    }
}
