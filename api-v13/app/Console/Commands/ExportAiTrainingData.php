<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\Sentence;
use App\Models\PaliSentence;
use App\Http\Api\MdRender;
use Illuminate\Support\Facades\File;
use App\Http\Api\ChannelApi;
use App\Services\PaliTextService;

class ExportAiTrainingData extends Command
{
    private $ShortTrans = 0.17;
    /**
     * The name and signature of the console command.
     * php artisan export:ai.training.data
     * @var string
     */
    protected $signature = 'export:ai.training.data {--format=gz  : zip file format 7z,lzma,gz } {--test}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'export ai training data';

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
        Log::info('task export offline sentence-table start');
        //创建文件夹
        $base = 'app/tmp/export/offline';
        $exportDir = storage_path($base);
        if (!is_dir($exportDir)) {
            $res = mkdir($exportDir, 0755, true);
            if (!$res) {
                $this->error('mkdir fail path=' . $exportDir);
                return 1;
            } else {
                $this->info('make dir successful ' . $exportDir);
            }
        }

        //创建临时文件夹\
        $dirname = $exportDir . '/' . 'wikipali-offline-ai-training-' . date("YmdHis");

        $tmp = mkdir($dirname, 0755, true);
        if (!$tmp) {
            $this->error('mkdir fail path=' . $dirname);
            return 1;
        } else {
            $this->info('make dir successful ' . $dirname);
        }

        $fpIndex = fopen($dirname . '/index.md', 'w');
        if ($fpIndex === false) {
            die('无法创建索引文件');
        }

        $channels = [
            '7ac4d13b-a43d-4409-91b5-5f2a82b916b3',
            'e5bc5c97-a6fb-4ccb-b7df-be6dcfee9c43',
            '74ebf4c5-c243-4948-955d-6c277e29276a',
            '3b0cb0aa-ea88-4ce5-b67d-00a3e76220cc',
            '5310999c-0b0c-4bb0-9bb9-9cdd176e9ef0',
            '331447b6-39bb-4b49-ac10-6206db93a050',
        ];

        $start = time();
        foreach ($channels as $key => $channel) {
            if ($this->option('test') && $key > 0) {
                // test mode 只跑一个
                break;
            }
            fwrite($fpIndex, "# {$channel}\n");
            $channelInfo = ChannelApi::getById($channel);
            if ($channelInfo) {
                fwrite($fpIndex, "- 版本名称：{$channelInfo['name']}\n");
                fwrite($fpIndex, "- 语言：{$channelInfo['lang']}\n");
            }
            // 创建文件
            $this->info('export start' . $channel);
            $filename = $channel . '.jsonl';
            $exportFile = $dirname . '/' . $filename;
            $fp = fopen($exportFile, 'w');
            if ($fp === false) {
                die('无法创建文件');
            }

            $db = Sentence::where('channel_uid', $channel);
            $bar = $this->output->createProgressBar($db->count());

            $srcDb = $db->select([
                'book_id',
                'paragraph',
                'word_start',
                'word_end',
                'content',
                'content_type',
                'updated_at'
            ])
                ->whereNotNull('content')
                ->orderBy('book_id')
                ->orderBy('paragraph')
                ->orderBy('word_start')->cursor();
            $done = [];
            foreach ($srcDb as $sent) {
                $id = "{$sent->book_id}-{$sent->paragraph}-{$sent->word_start}-{$sent->word_end}";
                if (isset($done[$id])) {
                    continue;
                }
                //获取原文
                $origin = PaliSentence::where('book', $sent->book_id)
                    ->where('paragraph', $sent->paragraph)
                    ->where('word_begin', $sent->word_start)
                    ->where('word_end', $sent->word_end)
                    ->value('text');
                //忽略空的原文
                if (self::isEmpty($origin)) {
                    Log::warning('origin is empty id=' . $id);
                    continue;
                }
                // 渲染译文
                $translation = MdRender::render(
                    $sent->content,
                    [$channel],
                    null,
                    'read',
                    'translation',
                    $sent->content_type,
                    'html',
                );
                $translation = trim($translation);
                // 忽略空的译文
                if (self::isEmpty($translation)) {
                    Log::warning('translation is empty id=' . $id);
                    continue;
                }

                //忽略过短的译文
                /*
                if (mb_strlen($translation) / mb_strlen($origin) < $this->ShortTrans) {
                    Log::warning('translation is short id=' . $id);
                    continue;
                }
                */
                //原文与翻译完全相同
                if ($translation === $origin) {
                    Log::warning('translation is same id=' . $id);
                    continue;
                }
                // 获取分类标签
                $paliTextService = app(PaliTextService::class);
                $tags = $paliTextService->getParaCategoryTags($sent->book_id, $sent->paragraph);
                $path = $paliTextService->getParaPathTitle($sent->book_id, $sent->paragraph);
                $currData = [
                    'id' => $id,
                    'original' => $origin,
                    'translation' => $translation,
                    'category' => $tags,
                    'path' => $path,
                    'updated_at' => $sent->updated_at
                ];

                fwrite($fp, json_encode($currData, JSON_UNESCAPED_UNICODE) . "\n");
                $bar->advance();
                $done[$id] = 1;
            }
            fclose($fp);
        }
        fclose($fpIndex);

        $this->info((time() - $start) . ' seconds');
        $this->call('export:zip2', [
            'id' => 'ai-translating-training-data',
            'filename' => $dirname,
            'title' => 'wikipali ai translating training data',
            'format' => $this->option('format'),
        ]);

        sleep(5);
        File::deleteDirectory($dirname);

        return 0;
    }

    private function isEmpty(?string $input): bool
    {
        if (empty($input)) {
            return true;
        }
        $result = preg_replace('/[\s\d\p{P}]/u', '', $input);
        return empty($result);
    }
}
