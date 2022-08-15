<?php
namespace App\Console\Commands;

require_once __DIR__."/../../../public/app/dict/turbo_split.php";

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
		$ts = new TurboSplit();
		$start = \microtime(true);

		$_word = $this->argument('word');
		if(!empty($_word)){
			var_dump($ts->splitA($_word));
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

		$words = WbwTemplate::select('real')->where('type','<>','.ctl.')->where('real','<>','')->groupBy('real')->cursor();
		$count = 0;
		foreach ($words as $key => $word) {
			# code...
			$count++;
			$this->info("{$count}:{$word->real}");
			$parts = $ts->splitA($word->real);
			foreach ($parts as $part) {
				$new = UserDict::firstOrNew(
					[
						'word' => $part['word'],
						'type' => ".cp.",
						'factors' => $part['factors'],
						'dict_id' => 'c42980f0-5967-4833-b695-84183344f68f'
					],
					[
						'id' => app('snowflake')->id(),
						'source' => '_ROBOT_',
						'create_time'=>(int)(microtime(true)*1000),
					]
				);
				$new->confidence = 50;
				$new->language = 'cm';
				$new->creator_id = 1;
				$new->flag = 1;
				$new->save();

			}
		}
		//删除旧数据
		UserDict::where('flag',0)->delete();
		UserDict::where('flag',1)->update(['flag'=>0]);
	
        return 0;
    }
}
