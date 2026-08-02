<?php

namespace App\Console\Commands;

use App\Http\Api\DictApi;
use App\Models\DhammaTerm;
use App\Models\UserDict;
use App\Services\OpenSearchService;
use Illuminate\Console\Command;

class ExportPaliSynonyms extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan export:pali.synonyms --output= [--test]
     *
     * @var string
     */
    protected $signature = 'export:pali.synonyms {--output=} {--test : 只导出一行，用于快速检查输出格式}';

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
        if (! $this->option('output')) {
            $this->error('please set output file option --output=file');

            return 1;
        }
        /*
        //irregular
        $dictId = ['4d3a0d92-0adc-4052-80f5-512a2603d0e8'];
        //regular
        $dictId[] = DictApi::getSysDict('system_regular');
        $dictId[] = DictApi::getSysDict('robot_compound');
*/
        $filename = $this->option('output');
        $fp = fopen($filename, 'w') or exit('Unable to open file!');

        $parents = UserDict::select('parent')
            ->whereNotNull('parent')
            ->where('parent', '<>', '')
            ->groupBy('parent')->cursor();

        $droppedTerms = 0;
        $droppedLines = 0;

        foreach ($parents as $parent) {
            if (str_contains($parent->parent, ' ')) {
                continue;
            }
            $words = UserDict::where('parent', $parent->parent)
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
            $this->info("[{$parent->parent}] ".count($words).' team='.count($teams));
            // 合并 $parent->parent, $words->word, $team->meaning 为一个字符串数组
            $combinedArray = [];
            $combinedArray[] = $parent->parent;
            foreach ($wordsList as $word => $value) {
                $combinedArray[] = $word;
            }

            // 过滤掉会被 analyzer 完全消除的 term，否则建索引时会失败
            $termCount = count($combinedArray);
            $combinedArray = $this->filterSynonymTerms($combinedArray);
            $droppedTerms += $termCount - count($combinedArray);

            // 同义词行至少要有两个 term 才有意义
            if (count($combinedArray) < 2) {
                $droppedLines++;

                continue;
            }

            // 将 $combinedArray 写入 CSV 文件
            fputcsv($fp, $combinedArray);

            if ($this->option('test')) {
                $this->warn('--test 已开启，只导出一行。');
                break;
            }
        }

        // 关闭文件
        fclose($fp);

        // 写index.json
        $info = [
            'index' => config('mint.opensearch.index'),
            'pali_synonyms' => app(OpenSearchService::class)->getPaliSynonymsSetting(),
        ];
        file_put_contents(
            $this->changeExtensionToJson($filename),
            json_encode($info, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
        $this->info("过滤掉非法 term {$droppedTerms} 个，整行丢弃 {$droppedLines} 行。");
        $this->info('done');

        return 0;
    }

    /**
     * 词典源码标记，这类 term 不是真正的词条
     *
     * 1. 含 # ‹ › 的：如 "#=cetayati)"、"(#=)(‹paññāpeti)"。
     *    其中以 # 开头的 term 若落在行首，整行会被 OpenSearch 当成注释静默忽略。
     * 2. 以 ( [ ＜ 开头的：如 "(ku的离格)"、"[ava-hīḷanā＜hīḍ]"，是词源/变格标注。
     *    只匹配开头，保证 "阿拉汉[果]"、"bhesajja[ṃ]" 这类正常释义不受影响。
     */
    private const DIRTY_TERM_PATTERN = '/[#‹›]|^[(\[＜<]/u';

    /**
     * 过滤掉不能用作同义词的 term，并按原顺序去重
     *
     * 丢弃两类 term：
     * 1. 不含任何字母或数字的（例如 "②"、"——"、"?)"）。OpenSearch 的 synonym_graph
     *    filter 会用 analyzer 分析每一个 term，这类 term 分析后被完全消除，
     *    建索引时会抛出 illegal_argument_exception: Failed to build synonyms。
     * 2. 带词典源码标记的脏词条，见 self::DIRTY_TERM_PATTERN。
     *
     * @param  array<int, string>  $terms
     * @return array<int, string>
     */
    private function filterSynonymTerms(array $terms): array
    {
        $seen = [];
        $filtered = [];
        foreach ($terms as $term) {
            $term = trim((string) $term);
            if ($term === '' || preg_match('/[\p{L}\p{Nd}]/u', $term) !== 1) {
                continue;
            }
            if (preg_match(self::DIRTY_TERM_PATTERN, $term) === 1) {
                continue;
            }
            if (isset($seen[$term])) {
                continue;
            }
            $seen[$term] = true;
            $filtered[] = $term;
        }

        return $filtered;
    }

    /**
     * 将给定文件路径的扩展名替换为 .json
     *
     * @param  string  $filePath  完整的文件路径
     * @return string 新的文件路径
     */
    private function changeExtensionToJson(string $filePath): string
    {
        // 获取路径信息
        $pathInfo = pathinfo($filePath);

        // 提取目录、文件名（不含扩展名）
        $dirname = $pathInfo['dirname'] ?? '';
        $filename = $pathInfo['filename'] ?? '';

        // 如果目录不是根目录，则添加目录分隔符
        $dirname = rtrim($dirname, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

        // 构建新路径
        return $dirname.$filename.'.json';
    }
}
