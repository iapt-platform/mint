<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\WordIndex;
use App\Models\WbwTemplate;
use App\Models\UserDict;
use App\Tools\TurboSplit;

class UpgradeCompound extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upgrade:compound {word?} {--test}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

	protected $dict_id = 'c42980f0-5967-4833-b695-84183344f68f';


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
		
		$start = \microtime(true);

		$_word = $this->argument('word');
		if(!empty($_word)){
			$ts = new TurboSplit();
			$results = $ts->splitA($_word);
			Storage::disk('local')->put("tmp/compound1.csv", "word,type,grammar,parent,factors");
			foreach ($results as $key => $value) {
				# code...
				Storage::disk('local')->append("tmp/compound1.csv", "{$value['word']},{$value['type']},{$value['grammar']},{$value['parent']},{$value['factors']}");
			}
			return 0;
		}

		//
		if($this->option('test')){
			//调试代码
			Storage::disk('local')->put("tmp/compound.md", "# Turbo Split");
			//获取需要拆的词
			$list = [
				[5,20,20],
				[21,30,20],
				[31,40,10],
				[41,60,10],
			];
			foreach ($list as $take) {
				# code...
				$words = WordIndex::where('final',0)->whereBetween('len',[$take[0],$take[1]])->select('word')->take($take[2])->get();
				foreach ($words as $word) {
					$this->info($word->word);
					Storage::disk('local')->append("tmp/compound.md", "## {$word->word}");
					$parts = $ts->splitA($word->word);
					foreach ($parts as $part) {
						# code...
						$this->info("{$part['word']},{$part['factors']},{$part['confidence']}");
						Storage::disk('local')->append("tmp/compound.md", "- `{$part['word']}`,{$part['factors']},{$part['confidence']}");
					}
				}
			}		
			$this->info("耗时：".\microtime(true)-$start);		
			return 0;	
		}

		//$words = WordIndex::where('final',0)->select('word')->orderBy('count','desc')->skip(72300)->cursor();
		$words = WbwTemplate::select('real')
						->where('book',118)
						->whereBetween('paragraph',[1329,1367])
						->where('type','<>','.ctl.')
						->where('real','<>','')
						->groupBy('real')->cursor();
		$count = 0;
		foreach ($words as $key => $word) {
			//先看目前字典里有没有
			$isExists = UserDict::where('word',$word->real)
								->where('dict_id',"<>",'8359757e-9575-455b-a772-cc6f036caea0')
								->exists();

			if($isExists){
				$this->info("found:{$word->real}");
				continue;
			}
			# code...
			$count++;
			$this->info("{$count}:{$word->real}"); 
			$ts = new TurboSplit();
			$parts = $ts->splitA($word->real);
			foreach ($parts as $part) {
				$new = UserDict::firstOrNew(
					[
						'word' => $part['word'],
						'factors' => $part['factors'],
						'dict_id' => $this->dict_id,
					],
					[
						'id' => app('snowflake')->id(),
						'source' => '_ROBOT_',
						'create_time'=>(int)(microtime(true)*1000),
					]
				);
				if(isset($part['type'])){
					$new->type = $part['type'];
				}else{
					$new->type = ".cp.";
				}
				if(isset($part['grammar'])) $new->parent = $part['grammar'];
				if(isset($part['parent'])) $new->parent = $part['parent'];
				$new->confidence = 50*$part['confidence'];
				$new->note = $part['confidence'];
				$new->language = 'cm';
				$new->creator_id = 1;
				$new->flag = 1;
				$new->save();
			}
		}
		//删除旧数据
		UserDict::where('dict_id',$this->dict_id)->where('flag',0)->delete();
		UserDict::where('dict_id',$this->dict_id)->where('flag',1)->update(['flag'=>0]);
	
        return 0;
    }
}
