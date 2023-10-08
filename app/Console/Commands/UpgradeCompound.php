<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\WordIndex;
use App\Models\WbwTemplate;
use App\Models\UserDict;
use App\Tools\TurboSplit;
use App\Http\Api\DictApi;
use Illuminate\Support\Facades\DB;

class UpgradeCompound extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upgrade:compound {word?} {--book=} {--debug} {--test} {--continue}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'auto split compound word';



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
        if(file_exists(base_path('.stop'))){
            $this->info('.stop exists');
            return 0;
        }
        $dict_id = DictApi::getSysDict('robot_compound');
        if(!$dict_id){
            $this->error('没有找到 robot_compound 字典');
            return 1;
        }

		$start = \microtime(true);

		$_word = $this->argument('word');
		if(!empty($_word)){
			$ts = new TurboSplit();
            if($this->option('debug')){
                $ts->debug(true);
            }
			$results = $ts->splitA($_word);
			Storage::disk('local')->put("tmp/compound1.csv", "word,type,grammar,parent,factors");
			foreach ($results as $key => $value) {
				# code...
                $output = "{$value['word']},{$value['type']},{$value['grammar']},{$value['parent']},{$value['factors']},{$value['confidence']}";
                $this->info($output);
				Storage::disk('local')->append("tmp/compound1.csv", $output);
			}
			return 0;
		}

		//
		if($this->option('test')){
			//调试代码
            $ts = new TurboSplit();
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
				$words = WordIndex::where('final',0)
                            ->whereBetween('len',[$take[0],$take[1]])
                            ->select('word')
                            ->take($take[2])->get();
				foreach ($words as $word) {
					$this->info($word->word);
					Storage::disk('local')->append("tmp/compound.md", "## {$word->word}");
					$parts = $ts->splitA($word->word);
					foreach ($parts as $part) {
						# code...
                        $info = "`{$part['word']}`,{$part['factors']},{$part['confidence']}";
						$this->info($info);
						Storage::disk('local')->append("tmp/compound.md", "- {$info}");
					}
				}
			}
			$this->info("耗时：".\microtime(true)-$start);
			return 0;
		}

        if($this->option('book')){
            $words = WbwTemplate::select('real')
                            ->where('book',$this->option('book'))
                            ->where('type','<>','.ctl.')
                            ->where('real','<>','')
                            ->orderBy('real')
                            ->groupBy('real')->cursor();
            $count = DB::select('SELECT count(*) from (
                                    SELECT "real" from wbw_templates where book = ? and type <> ? and real <> ? group by real) T',
                                    [$this->option('book'),'.ctl.','']);
        }else{
            $words = WbwTemplate::select('real')
                            ->where('type','<>','.ctl.')
                            ->where('real','<>','')
                            ->orderBy('real')
                            ->groupBy('real')->cursor();
            $count = DB::select('SELECT count(*) from (
                SELECT "real" from wbw_templates where type <> ? and real <> ? group by real) T',
                ['.ctl.','']);
        }

		$bar = $this->output->createProgressBar($count[0]->count);
		foreach ($words as $key => $word) {
            $bar->advance();
			if($this->option('continue')){
                //先看目前字典里有没有已经拆过的这个词
                $isExists = UserDict::where('word',$word->real)
                                    ->where('dict_id',$dict_id)
                                    ->where('flag',1)
                                    ->exists();
                if($isExists){
                   continue;
                }
			}
            //删除该词旧数据
            UserDict::where('word',$word->real)
                    ->where('dict_id',$dict_id)
                    ->delete();

			$ts = new TurboSplit();

            $parts = $ts->splitA($word->real);
            foreach ($parts as $part) {
                if(isset($part['type']) && $part['type'] === ".v."){
                    continue;
                }
                $new = UserDict::firstOrNew(
                    [
                        'word' => $part['word'],
                        'factors' => $part['factors'],
                        'dict_id' => $dict_id,
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
                if(isset($part['grammar'])) $new->grammar = $part['grammar'];
                if(isset($part['parent'])) $new->parent = $part['parent'];
                $new->confidence = 50*$part['confidence'];
                $new->note = $part['confidence'];
                $new->language = 'cm';
                $new->creator_id = 1;
                $new->flag = 1;//标记为维护状态
                $new->save();
            }
            if(env('APP_ENV','local') !== 'local'){
                usleep(500);
            }

		}
		//维护状态数据改为正常状态
		UserDict::where('dict_id',$dict_id)->where('flag',1)->update(['flag'=>0]);
        $bar->finish();
        return 0;
    }
}
