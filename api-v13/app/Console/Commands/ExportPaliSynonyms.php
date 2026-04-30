<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Api\DictApi;
use App\Models\UserDict;
use App\Models\DhammaTerm;

class ExportPaliSynonyms extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan export:pali.synonyms --output=
     * @var string
     */
    protected $signature = 'export:pali.synonyms {--output=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '导出openSearch用的巴利语变格表';

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
        if (!$this->option('output')) {
            $this->error('please set output file option --output=file');
            return 1;
        }
        //irregular
        $dictId = ['4d3a0d92-0adc-4052-80f5-512a2603d0e8'];
        //regular
        $dictId[] = DictApi::getSysDict('system_regular');

        $filename = $this->option('output');
        $fp = fopen($filename, 'w') or die("Unable to open file!");
        foreach ($dictId as  $dict) {
            $parents = UserDict::where('dict_id', $dict)
                ->select('parent')
                ->groupBy('parent')->cursor();

            foreach ($parents as  $parent) {
                $words = UserDict::where('dict_id', $dict)
                    ->where('parent', $parent->parent)
                    ->select('word')
                    ->groupBy('word')->get();
                $wordsList = [];
                foreach ($words as $word) {
                    $wordsList[$word->word] = 1;
                }
                $teams = DhammaTerm::where('word', $parent->parent)
                    ->select(['meaning'])->get();
                foreach ($teams as $term) {
                    $wordsList[$term->meaning] = 1;
                }
                $this->info("[{$parent->parent}] " . count($words) . " team=" . count($teams));
                // 合并 $parent->parent, $words->word, $team->meaning 为一个字符串数组
                $combinedArray = [];
                $combinedArray[] = $parent->parent;
                foreach ($wordsList as $word => $value) {
                    $combinedArray[] = $word;
                }

                // 将 $combinedArray 写入 CSV 文件
                fputcsv($fp, $combinedArray);
            }
        }

        // 关闭文件
        fclose($fp);
        $this->info('done');
        return 0;
    }
}
