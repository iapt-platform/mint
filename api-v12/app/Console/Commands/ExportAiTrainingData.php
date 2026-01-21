<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\Sentence;
use App\Models\PaliSentence;
use Illuminate\Support\Str;
use App\Http\Api\MdRender;

class ExportAiTrainingData extends Command
{
    /**
     * The name and signature of the console command.
     *
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
        $exportDir = storage_path('app/tmp/export/offline');
        if (!is_dir($exportDir)) {
            $res = mkdir($exportDir, 0755, true);
            if (!$res) {
                $this->error('mkdir fail path=' . $exportDir);
                return 1;
            } else {
                $this->info('make dir successful ' . $exportDir);
            }
        }
        $filename = 'wikipali-offline-ai-training-' . date("Y-m-d") . '.tsv';
        $exportFile = storage_path('app/tmp/export/offline/' . $filename);
        $fp = fopen($exportFile, 'w');
        if ($fp === false) {
            die('无法创建文件');
        }

        $channels = [
            '19f53a65-81db-4b7d-8144-ac33f1217d34',
        ];
        $start = time();
        foreach ($channels as $key => $channel) {
            $db = Sentence::where('channel_uid', $channel);
            $bar = $this->output->createProgressBar($db->count());
            $srcDb = $db->select([
                'book_id',
                'paragraph',
                'word_start',
                'word_end',
                'content',
                'content_type'
            ])->cursor();
            foreach ($srcDb as $sent) {
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
                $currData = array(
                    str_replace("\n", "", $origin),
                    str_replace("\n", "", $content),
                );

                fwrite($fp, implode("\t", $currData) . "\n");

                $bar->advance();
            }
        }
        fclose($fp);
        $this->info((time() - $start) . ' seconds');
        $this->call('export:zip', [
            'id' => 'ai-translating-training-data',
            'filename' => $exportFile,
            'title' => 'wikipali ai translating training data',
            'format' => $this->option('format'),
        ]);
        return 0;
    }
}
