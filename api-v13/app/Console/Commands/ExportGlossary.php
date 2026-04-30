<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use App\Services\TermService;


class ExportGlossary extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan export:export-glossary zh-Hans
     * @var string
     */
    protected $signature = 'export:export-glossary {lang}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '导出术语表';

    protected TermService $termService;

    public function __construct(TermService $termService)
    {
        $this->termService = $termService;
        parent::__construct();
    }
    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info('task export offline sentence-table start');
        $lang = $this->argument('lang');
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
        $dirname = $exportDir . '/' . 'wikipali_glossary_' . date("YmdHis");

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

        // 创建json文件
        $this->info('export start' . $lang);
        $filename = 'glossary_' . $lang . '.jsonl';
        $exportFile = $dirname . '/' . $filename;
        $fp = fopen($exportFile, 'w');
        if ($fp === false) {
            die('无法创建文件');
        }
        $start = time();

        //**业务逻辑 */

        $data = $this->termService->getCommunityGlossary($lang);
        foreach ($data['items'] as $key => $value) {
            fwrite($fp, json_encode($value, JSON_UNESCAPED_UNICODE) . "\n");
        }

        fclose($fpIndex);

        $this->info((time() - $start) . ' seconds');
        $this->call('export:zip2', [
            'id' => 'wikipali_glossary',
            'filename' => $dirname,
            'title' => 'wikipali glossary of community',
            'format' => 'jsonl',
        ]);

        sleep(5);
        File::deleteDirectory($dirname);

        return 0;
    }
}
