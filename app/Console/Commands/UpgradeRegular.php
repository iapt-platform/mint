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

class UpgradeRegular extends Command
{
    /**
     * The name and signature of the console command.
     *
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
        $dict_id = DictApi::getSysDict('system_regular');
        if(!$dict_id){
            $this->error('没有找到 system_regular 字典');
            return 1;
        }
		$nounEnding = array();
		$rowCount=0;
		if(($handle=fopen(public_path('app/public/ending/noun.csv'),'r'))!==FALSE){
			while(($data=fgetcsv($handle,0,','))!==FALSE){
				$rowCount++;
				if($rowCount==1) continue;//忽略首行
				array_push($nounEnding,$data);
			}
		}
		fclose($handle);

		$adjEnding = array();
		$rowCount=0;
		if(($handle=fopen(public_path('app/public/ending/adj.csv'),'r'))!==FALSE){
			while(($data=fgetcsv($handle,0,','))!==FALSE){
				$rowCount++;
				if($rowCount==1) continue;//忽略首行
				array_push($adjEnding,$data);
			}
		}
		fclose($handle);

		$verbEnding = array();
		$rowCount=0;
		if(($handle=fopen(public_path('app/public/ending/verb.csv'),'r'))!==FALSE){
			while(($data=fgetcsv($handle,0,','))!==FALSE){
				$rowCount++;
				if($rowCount==1) continue;//忽略首行
				array_push($verbEnding,$data);
			}
		}
		fclose($handle);

		if(empty($this->argument('word'))){
			$words = UserDict::where('type','.n:base.')
							->orWhere('type','.v:base.')
							->orWhere('type','.adj:base.')
							->orWhere('type','.ti:base.');
		}else{
			$words = UserDict::where('word',$this->argument('word'))
							->where(function($query) {
								$query->where('type','.n:base.')
								->orWhere('type','.v:base.')
								->orWhere('type','.adj:base.')
								->orWhere('type','.ti:base.');
							});
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

/*
		$words = UserDict::where('word','ābandhattalakkhaṇa')
				->select(['word','type','grammar'])
				->groupBy(['word','type','grammar']);
		$bar = $this->output->createProgressBar(1);
*/
		foreach ($words->cursor() as $word) {
			# code...
			switch($word->type){
				case ".v:base.":
					$casetable=$verbEnding;
					break;
				case ".n:base.":
					$casetable = $nounEnding;
						break;
				case ".ti:base.":
				case ".adj:base.":
					$casetable = $adjEnding;
					break;
				case "":
					$casetable=false;
					break;
				default:
					$casetable=false;
					break;
			}
			if($casetable === false){
				continue;
			}
			if($this->option('debug'))  $this->info("{$word->word}:{$word->type}");
			foreach($casetable as $thiscase){
				if($word->type==".v:base."){
					$endLen = (int)$thiscase[0];
					$head = mb_substr($word->word,0,(0-$endLen),"UTF-8");//原词剩余的部分
					$newEnding = $thiscase[1];
					$newGrammar = $thiscase[2];
					$newword=$head.$thiscase[1];
					//动词不做符合规则判定
					$isMatch = true;
				}else{
					$endLen = (int)$thiscase[5];
					$end = mb_substr($word->word,0-$endLen,NULL,"UTF-8");//原词被切下来的部分
					$head = mb_substr($word->word,0,(0-$endLen),"UTF-8");//原词剩余的部分
					$newEnding = $thiscase[3];
					$newGrammar = $thiscase[4];
					$newword=$head.$thiscase[2];
					if($word->type==".n:base."){
						//名词
						if($thiscase[0]==$word->grammar  && $thiscase[1]==$end){
							//符合规则判定成功
							$isMatch = true;
						}else{
							$isMatch = false;
						}
					}else{
						//形容词
						if($thiscase[1]==$end){
							//符合规则判定成功
							$isMatch = true;
						}else{
							$isMatch = false;
						}
					}

				}

				if($isMatch){
					if($this->option('debug'))  $this->error($newword.':match');
					//查询这个词是否在三藏存在
					$exist = Cache::remember('palicanon/word/exists/'.$newword, 100 , function() use($newword) {
						return WbwTemplate::where('real',$newword)->exists();
					});
					if($exist){
						if($this->option('debug'))  $this->info('exist');
						$new = UserDict::firstOrNew(
							[
								'word' => $newword,
								'type' => \str_replace(':base','',$word->type),
								'grammar' => $newGrammar,
								'parent' => $word->word,
								'factors' => "{$word->word}+[{$newEnding}]",
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
					}else{
						if($this->option('debug'))  $this->info('not exist');
					}
				}
			}
			$bar->advance();
		}
		$bar->finish();
		//删除旧数据
		$delOld = UserDict::where('dict_id',$dict_id);
		if(!empty($this->argument('word'))){
			$delOld = $delOld->where('word',$this->argument('word'));
		}
		$delOld->where('flag',0)->delete();
		$delOld->where('flag',1)->update(['flag'=>0]);
        return 0;
    }
}
