<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ImportArticle extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan import:article --token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJuYmYiOjE2OTc3Mjg2ODUsImV4cCI6MTcyOTI2NDY4NSwidWlkIjoiYmE1NDYzZjMtNzJkMS00NDEwLTg1OGUtZWFkZDEwODg0NzEzIiwiaWQiOjR9.fiXhnY2LczZ9kKVHV0FfD3AJPZt-uqM5wrDe4EhToVexdd007ebPFYssZefmchfL0mx9nF0rgHSqjNhx4P0yDA --studio=visuddhinanda --anthology=eb9e3f7f-b942-4ca4-bd6f-b7876b59a523
     * @var string
     */
    protected $signature = 'import:article {--token=} {--studio=} {--anthology=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '导入缅文tipitaka sarupa文章';

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

        //导入文章
        $url = config('app.url').'/api/v2/article';
        $inputRow = 0;
        fseek($fp, 0);
        $count = 0;
        while (($data = fgetcsv($fp, 0, ',')) !== false) {
            if($inputRow>0){
                $id = $data[0];
                $dir = $data[1];
                $title = $data[2];
                $realTitle = "[{$id}]{$title}";
                $content = str_replace('\n',"\n",$data[4]) ;
                $reference = str_replace(['(',')'],['({{ql|title=','}})'],$data[5]);
                $contentCombine = "{$title}\n\n{$content}\n\n{$reference}";
                $percent = (int)($inputRow*100/7000);
                $this->info("[{$percent}%] do ".$title);
                //先查是否有
                if(!isset($allTitles[$realTitle])){
                    $count++;
                    $this->info('没有，新建 title='.$realTitle);
                    $response = Http::withToken($token)->post($url,
                                    [
                                        'title'=> $realTitle,
                                        'lang'=> 'my',
                                        'studio'=> $studioName,
                                        'anthologyId'=> $anthologyId,
                                        'parentNode'=>$titles[$dir],
                                    ]);
                    sleep(1);
                    if($response->ok()){
                        $this->info('create ok');
                        $articleId = $response->json('data')['uid'];
                    }else{
                        $this->error('create fail');
                        Log::error('create fail title='.$title);
                        continue;
                    }

                    $this->info('修改 id='.$articleId);
                    $response = Http::withToken($token)->put($url.'/'.$articleId,
                                        [
                                            'title'=> $realTitle,
                                            'subtitle'=> $realTitle,
                                            'lang'=> 'my',
                                            'content'=> $contentCombine,
                                            'anthology_id'=>$anthologyId,
                                            'to_tpl'=>true,
                                            'status'=>30,
                                        ]);

                    if($response->ok()){
                        $this->info('edit ok');
                    }else{
                        $this->error('edit fail');
                        Log::error('edit fail id='.$articleId);
                        continue;
                    }
                }else{
                    $this->error('已经存在 title='.$realTitle);
                    $articleId = $allTitles[$realTitle];
                }
            }
            $inputRow++;
        }

        fclose($fp);
        return 0;
    }
}
