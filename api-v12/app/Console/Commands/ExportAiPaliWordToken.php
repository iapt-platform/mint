<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Api\DictApi;
use Illuminate\Support\Facades\Log;
use App\Models\UserDict;

class ExportAiPaliWordToken extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan export:ai.pali.word.token
     * @var string
     */
    protected $signature = 'export:ai.pali.word.token {--format=gz  : zip file format 7z,lzma,gz }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'export ai pali word token';

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
        Log::debug('export ai pali word token');

        if (\App\Tools\Tools::isStop()) {
            return 0;
        }
        $exportDir = storage_path('app/tmp/export/offline');
        if (!is_dir($exportDir)) {
            $res = mkdir($exportDir, 0755, true);
            if (!$res) {
                Log::error('mkdir fail path=' . $exportDir);
                return 1;
            } else {
                Log::info('make dir successful ' . $exportDir);
            }
        }

        $dict_id = DictApi::getSysDict('system_preference');
        if (!$dict_id) {
            Log::error('没有找到 system_preference 字典');
            return 1;
        }

        $filename = 'ai-pali-word-token-' . date("Y-m-d") . '.tsv';
        $exportFile = $exportDir . '/' . $filename;
        $fp = fopen($exportFile, 'w');
        if ($fp === false) {
            Log::error('无法创建文件');
            return 1;
        }

        $start = time();
        $total = UserDict::where('dict_id', $dict_id)->count();
        $words = UserDict::where('dict_id', $dict_id)
            ->select([
                'word',
                'factors',
            ])->cursor();
        foreach ($words as $key => $word) {
            $output = array($word->word, $word->factors);
            fwrite($fp, implode("\t", $output) . "\n");
            if ($key % 100 === 0) {
                $present = (int)($key * 100 / $total);
                $this->info("[{$present}%]-{$key}");
            }
        }
        fclose($fp);
        Log::info((time() - $start) . ' seconds');

        $this->call('export:zip', [
            'id' => 'ai-pali-word-token',
            'filename' => $exportFile,
            'title' => 'ai pali word token',
            'format' => $this->option('format'),
        ]);

        return 0;
    }
}
