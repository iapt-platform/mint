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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class UpgradeCompound extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan upgrade:compound --api=https://staging.wikipali.org/api
     * @var string
     */
    protected $signature = 'upgrade:compound {word?} {--book=} {--debug} {--test} {--continue} {--api=} {--from=} {--to=}';

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
        if(\App\Tools\Tools::isStop()){
            return 0;
        }
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


		$_word = $this->argument('word');
		if(!empty($_word)){
            $words = array((object)array('real'=>$_word));
            $count = 1;
		}else if($this->option('book')){
            $words = WbwTemplate::select('real')
                            ->where('book',$this->option('book'))
                            ->where('type','<>','.ctl.')
                            ->where('real','<>','')
                            ->orderBy('real')
                            ->groupBy('real')->cursor();
            $query = DB::select('SELECT count(*) from (
                                    SELECT "real" from wbw_templates where book = ? and type <> ? and real <> ? group by real) T',
                                    [$this->option('book'),'.ctl.','']);
            $count = $query[0]->count;
        }else{
            $min = WordIndex::min('id');
            $max = WordIndex::max('id');
            if($this->option('from')){
                $min = $min + $this->option('from');
            }
            $words = WordIndex::whereBetween('id',[$min,$max])
                            ->where('len','>',7)
                            ->orderBy('id')
                            ->selectRaw('word as real')
                            ->cursor();
            $count = $max - $min + 1;
        }

		$sn = 0;
        $wordIndex = array();
        $result = array();
		foreach ($words as $key => $word) {
            if(\App\Tools\Tools::isStop()){
                return 0;
            }
            $startAt = microtime(true);

			$ts = new TurboSplit();
            if($this->option('debug')){
                $ts->debug(true);
            }
            $wordIndex[] = $word->real;
            $parts = $ts->splitA($word->real);
            $time = round(microtime(true) - $startAt,2);
            $percent = (int)($sn * 100 / $count);
            $this->info("[{$percent}%] {$word->real}  {$time}s");

            $resultCount = 0;
            foreach ($parts as $part) {
                if(isset($part['type']) && $part['type'] === ".v."){
                    continue;
                }
                $resultCount++;
                $new = array();
                $new['word'] = $part['word'];
                $new['factors'] = $part['factors'];
                if(isset($part['type'])){
                    $new['type'] = $part['type'];
                }else{
                    $new['type'] = ".cp.";
                }
                if(isset($part['grammar'])){
                    $new['grammar'] = $part['grammar'];
                }else{
                    $new['grammar'] = null;
                }
                if(isset($part['parent'])){
                    $new['parent'] = $part['parent'];
                }else{
                    $new['parent'] = null;
                }
                $new['confidence'] = 50*$part['confidence'];
                $result[] = $new;

                if(!empty($_word)){
                    $output = "[{$resultCount}],{$part['word']},{$part['type']},{$part['grammar']},{$part['parent']},{$part['factors']},{$part['confidence']}";
                    $this->info($output);
                }
            }

            if(count($wordIndex) % 100 ===0){
                $this->upload($wordIndex,$result,$this->option('api'));
                $wordIndex = array();
                $result = array();
            }
		}
        $this->upload($wordIndex,$result,$this->option('api'));
        return 0;
    }

    private function upload($index,$words,$url=null){

        if(!$url){
            $url = config('app.url').'/api/v2/compound';
        }else{
            $url = $url.'/v2/compound';
        }
        $this->info('url = '.$url);
        $this->info('uploading size=s'.strlen(json_encode($words,JSON_UNESCAPED_UNICODE)));
        $response = Http::post($url,
                                [
                                    'index'=> $index,
                                    'words'=> $words,
                                ]);
        if($response->ok()){
            $this->info('upload ok');
        }else{
            $this->error('upload fail.');
        }
    }
}
