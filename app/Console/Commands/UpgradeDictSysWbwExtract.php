<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserDict;

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
		$dict  = UserDict::select('word')->where('word','!=','')->where('dict_id','ef620a93-a55d-4756-89c5-e188ab009e45')->groupBy('word');
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
					->where('dict_id','ef620a93-a55d-4756-89c5-e188ab009e45')
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
					->where('dict_id','ef620a93-a55d-4756-89c5-e188ab009e45')
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
						->where('dict_id','ef620a93-a55d-4756-89c5-e188ab009e45')
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
						'dict_id' => '85dcc61c-c9e1-4ae0-9b44-cd6d9d9f0d01', 
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
        return 0;
    }
}
