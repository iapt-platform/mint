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
     * 分两个步骤导入
     * 1. 导入文章到文集
     * 2. 重新生成目录结构
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
        $head = array();
        $strFileName = __DIR__."/tipitaka-sarupa.csv";
        if(!file_exists($strFileName)){
            $this->error($strFileName.'文件不存在');
            return 1;
        }

        if (($fp = fopen($strFileName, "r")) === false) {
            $this->error("can not open csv {$strFileName}");
            return 0;
        }
        $this->info('打开csv文件成功');

        $studioId = App\Http\Api\StudioApi::getIdByName($studioName);
        if(!$studioId){
            $this->error("can not found studio name {$studioName}");
            return 0;
        }
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
                $this->info("[{$percent}%] doing ".$realTitle);
                //先查是否有
                $hasArticle = Article::where('owner',$studioId)
                              ->where('title',$realTitle)
                              ->exists();
                if($hasArticle){
                    $this->error('文章已经存在 title='.$realTitle);
                    continue;
                }
                $count++;
                $this->info('新建 title='.$realTitle);
                $response = Http::withToken($token)->post($url,
                                [
                                    'title'=> $realTitle,
                                    'lang'=> 'my',
                                    'studio'=> $studioName,
                                    'anthologyId'=> $anthologyId,
                                ]);
                sleep(1);
                if($response->ok()){
                    $this->info('create ok');
                    $articleId = $response->json('data')['uid'];
                }else{
                    $this->error('create article fail.'.$realTitle);
                    Log::error('create article fail title='.$realTitle);
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
                    $this->error('edit article fail');
                    Log::error('edit article fail ',['id'=>$articleId,'title'=>$realTitle]);
                }
            }
            $inputRow++;
        }

        fclose($fp);
        return 0;
    }
}
