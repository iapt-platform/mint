<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Api\DictApi;
use App\Models\UserDict;
use App\Models\WordIndex;
use Carbon\Carbon;

class UpgradeDictSysPreference extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     * php artisan upgrade:dict.sys.preference
     */
    protected $signature = 'upgrade:dict.sys.preference';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'upgrade dict system preference';

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
            return 0;
        }
        $this->info("start");
        $dictList = [
            'community_extract',
            'robot_compound',
            'system_regular',
            'system_preference',
        ];
        $dict_id = array();
        foreach ($dictList as $key => $value) {
            $dict_id[$value] = DictApi::getSysDict($value);
            if (!$dict_id[$value]) {
                $this->error("没有找到 {$value} 字典");
                return 1;
            } else {
                $this->info("{$value} :{$dict_id[$value]}");
            }
        }
        if (!$this->confirm('继续吗？')) {
            return 0;
        }
        //搜索顺序
        $order = [
            '4d3a0d92-0adc-4052-80f5-512a2603d0e8',/* system irregular */
            $dict_id['community_extract'],/* 社区字典*/
            $dict_id['robot_compound'],
            $dict_id['system_regular'],
        ];
        $words = WordIndex::orderBy('count', 'desc')->cursor();
        $rows = 0;
        $found = 0;
        foreach ($words as $key => $word) {
            if (preg_match('/\d/', $word->word)) {
                //不处理带数字的
                continue;
            }
            $rows++;
            $factors = null;
            $parent = null;
            $confidence = 0;
            foreach ($order as $key => $dict) {
                //找到第一个有拆分的
                $preWords = UserDict::where('word', $word->word)
                    ->where('dict_id', $dict)
                    ->orderBy('confidence', 'desc')
                    ->get();
                foreach ($preWords as $key => $value) {
                    if (!$factors && !empty($value->factors)) {
                        $factors = $value->factors;
                        if ($value->confidence > $confidence) {
                            $confidence = $value->confidence;
                        }
                    }
                    if (!$parent && !empty($value->parent)) {
                        $parent = $value->parent;
                        if ($value->confidence > $confidence) {
                            $confidence = $value->confidence;
                        }
                    }
                    if ($parent && $factors) {
                        break;
                    }
                }
            }
            if ($parent || $factors) {
                $prefWord = UserDict::where('word', $word->word)
                    ->where('dict_id', $dict_id['system_preference'])
                    ->first();
                if (!$prefWord) {
                    $prefWord = new UserDict();
                    $prefWord->word = $word->word;
                    $prefWord->dict_id = $dict_id['system_preference'];
                    $prefWord->id = app('snowflake')->id();
                    $prefWord->source = '_ROBOT_';
                    $prefWord->create_time = (int)(microtime(true) * 1000);
                    $prefWord->language = 'cm';
                    $prefWord->creator_id = 1;
                } else {
                    if (Carbon::parse($prefWord->updated_at) > Carbon::parse($prefWord->created_at)) {
                        //跳过已经被编辑过的。
                        $this->info('跳过已经被编辑过的' . $word->word);
                        continue;
                    }
                }
                $prefWord->factors = $factors;
                $prefWord->parent = $parent;
                $prefWord->confidence = $confidence;
                $prefWord->save();
                $found++;
            }
            if ($rows % 100 == 0) {
                $output = "[{$rows}] {$word->word} found:{$found}";
                $this->info($output);
                $found = 0;
            }
        }
        return 0;
    }
}
