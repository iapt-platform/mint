<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ImportArticleMap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:article.map {--token=} {--studio=} {--anthology=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '重置缅文tipitaka sarupa文章目录';

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
        if (!$this->confirm('Do you wish to continue?')) {
            return 0;
        }
        $token = $this->option('token');
        $studioName = $this->option('studio');
        $anthologyId = $this->option('anthology');

        //先获取文章列表，建立全部目录
        $url = config('app.url').'/api/v2/article-map';
        $response = Http::get($url,[
            'view'=>'anthology',
            'id'=>$anthologyId,
        ]);
        if($response->failed()){
            $this->error('获取文章列表 fail');
            return 0;
        }else{
            $this->info('获取文章列表 ok');
        }
        if(!$response->json('ok')){
            $this->error('http request error'.$response->json('message'));
            return 0;
        }
        $articles = $response->json('data.rows');
        $this->info('打开csv文件并读取数据');
        $head = array();
        $strFileName = __DIR__."/tipitaka-sarupa.csv";
        if(!file_exists($strFileName)){
            $this->error($strFileName.'文件不存在');
            return 1;
        }

        if (($fp = fopen($strFileName, "r")) === false) {
            $this->error("can not open csv $strFileName");
            return 0;
        }
        $inputRow = 0;
        while (($data = fgetcsv($fp, 0, ',')) !== false) {
            if($inputRow>0){
                if(!isset($head[$data[1]])){
                    $head[$data[1]] = 1;
                }
            }
            $inputRow++;
        }

        $this->info("csv head=" .count($head));

        $titles = array();
        $allTitles = array();
        foreach ($articles as $key => $article) {
            $allTitles[$article['title']] = $article['article_id'];
            if($article['level']===1 && isset($head[$article['title']])){
                $titles[$article['title']] = $article['article_id'];
                $this->info('已有='.$article['title']);
            }
        }
        $this->info("article map head=" .count($titles));
        //新建目录
        foreach ($head as $key => $value) {
            if(!isset($titles[$key])){
                $this->info('没有key'.$key.'新建');
                $url = config('app.url').'/api/v2/article';
                $response = Http::withToken($token)->post($url,
                                [
                                    'title'=> $key,
                                    'lang'=> 'my',
                                    'studio'=> $studioName,
                                    'anthologyId'=> $anthologyId,
                                ]);
                if($response->ok()){
                    $this->info('append ok title='.$key);
                    $articleId = $response->json('data')['uid'];
                    $titles[$key] = $articleId;
                }else{
                    $this->error('append fail');
                    Log::error('append fail');
                }
            }
        }
        print_r($titles);
        return 0;
    }
}
