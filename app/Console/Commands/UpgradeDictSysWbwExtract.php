<?php
/**
 * 将用户词典中的数据进行汇总。
 * 算法：
 * 同样词性的合并为一条记录。意思按照出现的次数排序
 */
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserDict;
use App\Http\Api\DictApi;

class UpgradeDictSysWbwExtract extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upgrade:syswbwextract';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '从社区词典中提取最优结果';

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
        $user_dict_id = DictApi::getSysDict('community');
        if(!$user_dict_id){
            $this->error('没有找到 community 字典');
            return 1;
        }
        $user_dict_extract_id = DictApi::getSysDict('community_extract');
        if(!$user_dict_extract_id){
            $this->error('没有找到 community_extract 字典');
            return 1;
        }
		$dict  = UserDict::select('word')->where('word','!=','')->where('dict_id',$user_dict_id)->groupBy('word');
		$bar = $this->output->createProgressBar($dict->count());
		foreach ($dict->cursor() as  $word) {
			# code...
			//case
			$wordtype = '';
			$wordgrammar = '';
			$wordparent = '';
			$wordfactors = '';

			$case = UserDict::selectRaw('type,grammar, sum(confidence)')
					->where('word',$word->word)
					->where('dict_id',$user_dict_id)
					->where('type','!=','.part.')
					->where('type','<>','')
					->whereNotNull('type')
					->groupBy(['type','grammar'])
					->orderBy('sum','desc')
					->first();
			if($case){
				$wordtype = $case->type;
				$wordgrammar = $case->grammar;
			}

			//parent
			$parent = UserDict::selectRaw('parent, sum(confidence)')
					->where('word',$word->word)
					->where('dict_id',$user_dict_id)
					->where('type','!=','.part.')
					->where('parent','!=','')
					->whereNotNull('parent')
					->groupBy('parent')
					->orderBy('sum','desc')
					->first();
			if($parent){
				$wordparent = $parent->parent;
			}


				//factors
				$factor = UserDict::selectRaw('factors, sum(confidence)')
						->where('word',$word->word)
						->where('dict_id',$user_dict_id)
						->where('type','!=','.part.')
						->where('factors','<>','')
						->whereNotNull('factors')
						->groupBy('factors')
						->orderBy('sum','desc')
						->first();
				if($factor){
					$wordfactors = $factor->factors;
				}
				$new = UserDict::firstOrNew(
					[
						'word' => $word->word,
						'type' => $wordtype,
						'grammar' => $wordgrammar,
						'parent' => $wordparent,
						'factors' => $wordfactors,
						'dict_id' => $user_dict_extract_id,
					],
					[
						'id' => app('snowflake')->id(),
						'source' => '_ROBOT_',
						'create_time'=>(int)(microtime(true)*1000)
					]
				);
				$new->confidence = 90;
				$new->language = 'cm';
				$new->creator_id = 1;
				$new->flag = 1;
				$new->save();

				$bar->advance();
		}
		$bar->finish();

        //TODO 删除旧数据
        return 0;
    }
}
