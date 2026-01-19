<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CreateMyHanCrop extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan create:my.han.crop --page=10
     * @var string
     */
    protected $signature = 'create:my.han.crop {--page=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '建立缅汉字典切图工作文件';

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
        $csvFile = config("mint.path.dict_text") . '/zh/my-han/index.csv';
        if (($fp = fopen($csvFile, "r")) !== false) {
            $row = 0;
            $currPage = 0;
            $currPageWords = [];
            while (($data = fgetcsv($fp, 0, ',')) !== false) {
                $row++;
                if ($row === 1) {
                    continue;
                }
                if ($this->option('page')) {
                    if ($currPage >= (int)$this->option('page')) {
                        break;
                    }
                }
                $page = (int)$data[1];
                $word = $data[2];
                if ($page !== $currPage) {
                    //保存上一页数据
                    $this->save($currPage, $currPageWords);
                    $currPage = $page;
                    //清空单词缓存
                    $currPageWords = [];
                }
                $currPageWords[] = $word;
            }
            fclose($fp);
            $this->save($currPage, $currPageWords);
        }
        $this->info('done');
        return 0;
    }
    private function save($page, $words)
    {
        $basicUrl = 'https://ftp.wikipali.org/kosalla/%E7%BC%85%E6%96%87%E8%AF%8D%E5%85%B8/';
        if (count($words) > 0) {
            $m = new \Mustache_Engine(array(
                'entity_flags' => ENT_QUOTES,
                'escape' => function ($value) {
                    return $value;
                }
            ));
            $tplFile = resource_path("/mustache/my_han_crop.tpl");
            $tpl = file_get_contents($tplFile);
            $wordWithIndex = [];
            foreach ($words as $key => $value) {
                $wordWithIndex[] = [
                    'index' => $key + 1,
                    'word' => trim($value),
                ];
            }
            $data = [
                'dict' => [
                    ['index' => 'a', 'img' => "{$basicUrl}{$page}A.jpg"],
                    ['index' => 'b', 'img' => "{$basicUrl}{$page}B.jpg"],
                    ['index' => 'a', 'img' => "{$basicUrl}" . ($page + 1) . "A.jpg"],
                ],
                'words' => $wordWithIndex
            ];
            $content = $m->render($tpl, $data);
            //保存到临时文件夹
            // 使用本地磁盘
            // 创建目录]
            $dir = '/tmp/export/myhan_crop/' . $page;
            Storage::disk('local')->makeDirectory($dir);
            Storage::disk('local')->makeDirectory($dir . '/img');
            Storage::disk('local')->put($dir . "/index.html", $content);
            Storage::disk('local')->put($dir . "/img/{$page}", $page);
            $this->info("page={$page} word=" . count($words));
        } else {
            $this->error('page' . $page . 'no words');
        }
    }
}
