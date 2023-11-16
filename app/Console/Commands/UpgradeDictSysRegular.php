<?php
/**
 * 生成系统规则变形词典
 * 算法： 扫描字典里的所有单词。根据语尾表变形。
 * 并在词库中查找是否在三藏中出现。出现的保存。
 */
namespace App\Console\Commands;

use App\Models\UserDict;
use App\Models\WbwTemplate;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Http\Api\DictApi;
use App\Tools\CaseMan;

class UpgradeDictSysRegular extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan upgrade:regular jāta
     * @var string
     */
    protected $signature = 'upgrade:regular {word?} {--debug}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'upgrade regular';
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
        $dict_id = DictApi::getSysDict('system_regular');
        if(!$dict_id){
            $this->error('没有找到 system_regular 字典');
            return 1;
        }else{
            $this->info("system_regular :{$dict_id}");
        }

		if(empty($this->argument('word'))){
			$words = UserDict::where('type','.n:base.')
							->orWhere('type','.v:base.')
							->orWhere('type','.adj:base.')
							->orWhere('type','.ti:base.');
            $init = UserDict::where('dict_id',$dict_id)
                            ->update(['flag'=>0]);
		}else{
			$words = UserDict::where('word',$this->argument('word'))
							->where(function($query) {
								$query->where('type','.n:base.')
								->orWhere('type','.v:base.')
								->orWhere('type','.adj:base.')
								->orWhere('type','.ti:base.');
							});
            $init = UserDict::where('dict_id',$dict_id)
                            ->where('word',$this->argument('word'))
                            ->update(['flag'=>0]);
		}
		$words = $words->select(['word','type','grammar'])
						->groupBy(['word','type','grammar'])
						->orderBy('word');
		$query = "
		select count(*) from (select count(*) from user_dicts ud where
			\"type\" = '.v:base.' or
			\"type\" = '.n:base.' or
			\"type\" = '.ti:base.' or
			\"type\" = '.adj:base.'
			group by word,type,grammar) as t;
		";
		$count = DB::select($query);
		$bar = $this->output->createProgressBar($count[0]->count);
        $caseMan = new CaseMan();
		foreach ($words->cursor() as $word) {
            if($this->option('debug')){$this->info("{$word->word}:{$word->type}");}
            $newWords = $caseMan->Declension($word->word,$word->type,$word->grammar,0.5);
            if($this->option('debug')){$this->info("{$word->word}:".count($newWords));}
            foreach ($newWords as $newWord) {
                if(isset($newWord['type'])){
                    $type = $newWord['type'];
                }else{
                    $type = \str_replace(':base','',$word->type);
                }

                $new = UserDict::firstOrNew(
                    [
                        'word' => $newWord['word'],
                        'type' => $type,
                        'grammar' => $newWord['grammar'],
                        'parent' => $word->word,
                        'factors' => $newWord['factors'],
                        'dict_id' => $dict_id,
                    ],
                    [
                        'id' => app('snowflake')->id(),
                        'source' => '_ROBOT_',
                        'create_time'=>(int)(microtime(true)*1000)
                    ]
                );
                $new->confidence = 80;
                $new->language = 'cm';
                $new->creator_id = 1;
                $new->flag = 1;
                $new->save();
            }

			$bar->advance();
		}
		$bar->finish();
        if(!empty($this->argument('word'))){
			$declensions = UserDict::where('dict_id',$dict_id)
                            ->where('parent',$this->argument('word'))
                            ->select('word')
                            ->groupBy('word')
                            ->get();
            foreach ($declensions as $key => $word) {
                Log::debug($word->word);
            }
		}
		//删除旧数据
		$table = UserDict::where('dict_id',$dict_id);
		if(!empty($this->argument('word'))){
			$table = $table->where('parent',$this->argument('word'));
		}
		$table->where('flag',0)->delete();

        //DB::enableQueryLog();
        $newRecord = UserDict::where('dict_id',$dict_id);
		if(!empty($this->argument('word'))){
			$newRecord = $newRecord->where('parent',$this->argument('word'));
		}
		$newRecord->where('flag',1)->update(['flag'=>0]);
        //print_r(DB::getQueryLog());
        return 0;
    }
}
