<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\Sentence;
use App\Models\PaliSentence;
use App\Http\Api\MdRender;
use Illuminate\Support\Facades\File;

class ExportAiTrainingData extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan export:ai.training.data
     * @var string
     */
    protected $signature = 'export:ai.training.data {--format=gz  : zip file format 7z,lzma,gz }';

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
        Log::debug('task export offline sentence-table start');
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


        $channels = [
            '19f53a65-81db-4b7d-8144-ac33f1217d34',
            'e5bc5c97-a6fb-4ccb-b7df-be6dcfee9c43',
            '7ac4d13b-a43d-4409-91b5-5f2a82b916b3',
            '74ebf4c5-c243-4948-955d-6c277e29276a',
            '3b0cb0aa-ea88-4ce5-b67d-00a3e76220cc',
            '5310999c-0b0c-4bb0-9bb9-9cdd176e9ef0',
            '331447b6-39bb-4b49-ac10-6206db93a050',
        ];

        $start = time();
        foreach ($channels as $key => $channel) {
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
                'content_type'
            ])->orderBy('book_id')
                ->orderBy('paragraph')
                ->orderBy('word_start')->cursor();
            $done = [];
            foreach ($srcDb as $sent) {
                $id = "{$sent->book_id}-{$sent->paragraph}-{$sent->word_start}-{$sent->word_end}";
                if (isset($done[$id])) {
                    continue;
                }
                $content = MdRender::render(
                    $sent->content,
                    [$channel],
                    null,
                    'read',
                    'translation',
                    $sent->content_type,
                    'text',
                );
                $origin = PaliSentence::where('book', $sent->book_id)
                    ->where('paragraph', $sent->paragraph)
                    ->where('word_begin', $sent->word_start)
                    ->where('word_end', $sent->word_end)
                    ->value('text');
                if (empty($origin)) {
                    Log::warning('origin is empty id=' . $id);
                    continue;
                }
                if (empty($content)) {
                    Log::warning('translation is empty id=' . $id);
                    continue;
                }
                $currData = ['id' => $id, 'original' => $origin, 'translation' => trim($content)];

                fwrite($fp, json_encode($currData, JSON_UNESCAPED_UNICODE) . "\n");
                $bar->advance();
                $done[$id] = 1;
            }
            fclose($fp);
        }

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
}
