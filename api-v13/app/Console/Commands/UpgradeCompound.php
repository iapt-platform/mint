<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\WordIndex;
use App\Models\WbwTemplate;
use App\Models\UserDict;
use Illuminate\Support\Facades\Log;

use App\Tools\TurboSplit;
use App\Http\Api\DictApi;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;


class UpgradeCompound extends Command
{
    /**
     * The name and signature of the console command.
     * php -d memory_limit=2024M artisan upgrade:compound  --api=https://next.wikipali.org/api --from=0 --to=500000
     * @var string
     */
    protected $signature = 'upgrade:compound {word?} {--book=} {--debug} {--test} {--continue} {--api=} {--from=0} {--to=0} {--min=7} {--max=50} {--timeout=600}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'auto split compound word';

    protected $MaxOneLoopTime = 120;

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
        if (\App\Tools\Tools::isStop()) {
            $this->info('.stop exists');
            return 0;
        }
        $confirm = '';
        if ($this->option('api')) {
            $confirm .= 'api=' . $this->option('api') . PHP_EOL;
        }
        $confirm .= "min=" . $this->option('min') . PHP_EOL;
        $confirm .= "max="  . $this->option('max') . PHP_EOL;
        $confirm .= "from="  . $this->option('from') . PHP_EOL;
        $confirm .= "to="  . $this->option('to') . PHP_EOL;

        if (!$this->confirm($confirm)) {
            return 0;
        }
        $this->info('[' . date('Y-m-d H:i:s', time()) . '] upgrade:compound start');

        $dict_id = DictApi::getSysDict('robot_compound');
        if (!$dict_id) {
            $this->error('没有找到 robot_compound 字典');
            return 1;
        }

        $start = \microtime(true);



        //
        if ($this->option('test')) {
            //调试代码
            $ts = new TurboSplit(['timeout' => $this->option('timeout')]);
            Storage::disk('local')->put("tmp/compound.md", "# Turbo Split");
            //获取需要拆的词
            $list = [
                [5, 20, 20],
                [21, 30, 20],
                [31, 40, 10],
                [41, 60, 10],
            ];
            foreach ($list as $take) {
                # code...
                $words = WordIndex::where('final', 0)
                    ->whereBetween('len', [$take[0], $take[1]])
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
            $this->info("耗时：" . \microtime(true) - $start);
            return 0;
        }

        $_word = $this->argument('word');
        if (!empty($_word)) {
            $words = array((object)array('real' => $_word, 'id' => 0));
            $total = 1;
        } else if ($this->option('book')) {
            $words = WbwTemplate::select('real')
                ->where('book', $this->option('book'))
                ->where('type', '<>', '.ctl.')
                ->where('real', '<>', '')
                ->orderBy('real')
                ->groupBy('real')->cursor();
            $query = DB::select(
                'SELECT count(*) from (
                                    SELECT "real" from wbw_templates where book = ? and type <> ? and real <> ? group by real) T',
                [$this->option('book'), '.ctl.', '']
            );
            $total = $query[0]->count;
        } else {
            $min = WordIndex::min('id');
            $max = WordIndex::max('id');
            if ($this->option('from') > 0) {
                $from = $min + $this->option('from');
            } else {
                $from = $min;
            }
            if ($this->option('to') > 0) {
                $to = $min + $this->option('to');
            } else {
                $to = $max;
            }
            $table = WordIndex::whereBetween('id', [$from, $to]);
            if ($this->option('min') > 0) {
                $table = $table->where('len', '>=', $this->option('min'));
            }
            if ($this->option('max') > 0) {
                $table = $table->where('len', '<=', $this->option('max'));
            }
            $total = $table->count();
            $words = $table->orderBy('id')
                ->selectRaw('id,word as real')
                ->cursor();
        }

        $wordIndex = array();
        $result = array();

        $loopTime = 0;
        foreach ($words as $key => $word) {
            $startAt = microtime(true);
            if (\App\Tools\Tools::isStop()) {
                $this->info('system stop');
                return 0;
            }
            $percent = (int)($key * 100 / $total);
            if (preg_match('/\d/', $word->real)) {
                $this->info("[{$percent}%] {$word->real} 数字不处理");
                continue;
            }
            //判断数据库里面是否有
            $exists = UserDict::where('dict_id', $dict_id)
                ->where('word', $word->real)
                ->exists();
            if ($exists) {
                $this->info("[{$percent}%]-{$key}-{$word->real}数据库中已经有了");
                continue;
            }

            $now = date('Y-m-d H:i:s');
            $this->info("[{$percent}%]-[{$now}]{$word->real} start id={$word->id}");
            $wordIndex[] = $word->real;

            //先查询vir数据有没有拆分
            $parts = array();
            $wbwWords = WbwTemplate::where('real', $word->real)
                ->select('word')->groupBy('word')->get();
            foreach ($wbwWords as $key => $wbwWord) {
                if (strpos($wbwWord->word, '-') !== false) {
                    $wbwFactors = explode('-', $wbwWord->word);
                    //看词尾是否能找到语尾
                    $endWord = end($wbwFactors);
                    $endWordInDict = UserDict::where('word', $endWord)->get();
                    foreach ($endWordInDict as $key => $oneWord) {
                        if (
                            !empty($oneWord->type) &&
                            strpos($oneWord->type, 'base') === false &&
                            $oneWord->type !== '.cp.'
                        ) {
                            $parts[] = [
                                'word' => $oneWord->real,
                                'type' => $oneWord->type,
                                'grammar' => $oneWord->grammar,
                                'parent' => $oneWord->parent,
                                'factors' => implode('+', array_slice($wbwFactors, 0, -1)) . '+' . $oneWord->factors,
                                'confidence' => 100,
                            ];
                        }
                    }
                }
            }
            if (count($parts) === 0) {
                $ts = new TurboSplit(['timeout' => $this->option('timeout')]);
                if ($this->option('debug')) {
                    $ts->debug(true);
                }
                $parts = $ts->splitA($word->real);
            } else {
                $this->info("找到vri拆分数据：" . count($parts));
            }

            $resultCount = 0;
            foreach ($parts as $part) {
                if (isset($part['type']) && $part['type'] === ".v.") {
                    continue;
                }
                if (empty($part['word'])) {
                    continue;
                }
                $resultCount++;
                $new = array();
                $new['word'] = $part['word'];
                $new['factors'] = $part['factors'];
                if (isset($part['type'])) {
                    $new['type'] = $part['type'];
                } else {
                    $new['type'] = ".cp.";
                }
                if (isset($part['grammar'])) {
                    $new['grammar'] = $part['grammar'];
                } else {
                    $new['grammar'] = null;
                }
                if (isset($part['parent'])) {
                    $new['parent'] = $part['parent'];
                } else {
                    $new['parent'] = null;
                }
                $new['confidence'] = 50 * $part['confidence'];
                $result[] = $new;

                if (!empty($_word)) {
                    //指定拆分单词输出结果
                    $debugOutput = [
                        $resultCount,
                        $part['word'],
                        $part['type'],
                        $part['grammar'],
                        $part['parent'],
                        $part['factors'],
                        $part['confidence']
                    ];
                    $this->info(implode(',', $debugOutput));
                }
            }

            $time = round(microtime(true) - $startAt, 2);
            $loopTime += $time;
            $this->info("[{$percent}%][{$key}] {$word->real}  {$time}s total{$loopTime}");

            if ($loopTime > $this->MaxOneLoopTime) {
                //到时间上传
                $ok = $this->upload($wordIndex, $result, $this->option('api'));
                if (!$ok) {
                    Log::error('break on ' . $word->id);
                    return 1;
                }
                $wordIndex = array();
                $result = array();
                $loopTime = 0;
            }
        }
        $this->upload($wordIndex, $result, $this->option('api'));

        $this->info('[' . date('Y-m-d H:i:s', time()) . '] upgrade:compound finished');

        return 0;
    }

    private function upload($index, $words, $url = null)
    {
        if (count($words) === 0) {
            return;
        }
        if (!$url) {
            $url = config('app.url') . '/api/v2/compound';
        } else {
            $url = $url . '/v2/compound';
        }
        $this->info('url = ' . $url);
        $this->info('uploading size=' . strlen(json_encode($words, JSON_UNESCAPED_UNICODE)));

        $httpError = false;
        $Max_Loop = 10;

        try {
            $response = Http::retry($Max_Loop, 100, function (Exception $exception) {
                Log::error('upload fail.', ['error' => $exception]);
                $this->error('upload fail. try again');
                return true;
            })
                ->post(
                    $url,
                    [
                        'index' => $index,
                        'words' => $words,
                    ]
                );
            if ($response->ok()) {
                $this->info('upload ok');
                $httpError = false;
            } else {
                $this->error('upload fail.');
                Log::error('upload fail.' . $response->body());
            }
        } catch (GuzzleException $e) {
            Log::error('send data failed', ['exception' => $e]);
            $httpError = true;
        }

        if ($httpError) {
            Log::error('upload fail.try max');
            return false;
        }
        return true;
    }
}
