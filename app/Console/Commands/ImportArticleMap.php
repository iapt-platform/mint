<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Http\Api\StudioApi;
use App\Models\Article;
use App\Models\Collection;

class ImportArticleMap extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan import:article.map visuddhinanda --studio=visuddhinanda --size=30000 --anthology=4c6b661b-fd68-44c5-8918-2e327c870b9a --token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJuYmYiOjE2OTc3Mjg2ODUsImV4cCI6MTcyOTI2NDY4NSwidWlkIjoiYmE1NDYzZjMtNzJkMS00NDEwLTg1OGUtZWFkZDEwODg0NzEzIiwiaWQiOjR9.fiXhnY2LczZ9kKVHV0FfD3AJPZt-uqM5wrDe4EhToVexdd007ebPFYssZefmchfL0mx9nF0rgHSqjNhx4P0yDA
     *
     * @var string
     */
    protected $signature = 'import:article.map {src_studio} {--token=} {--studio=} {--anthology=} {--size=}';

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
        $token = $this->option('token');
        $studioName = $this->option('studio');
        $anthologyId = $this->option('anthology');
        $srcStudio = $this->argument('src_studio');
        if (!$this->confirm('Do you wish to continue?')) {
            return 0;
        }
        $studioId = StudioApi::getIdByName($studioName);
        if(!$studioId){
            $this->error("can not found studio name {$studioName}");
            return 0;
        }
        $srcStudioId = StudioApi::getIdByName($srcStudio);
        if(!$srcStudioId){
            $this->error("can not found src studio name {$srcStudio}");
            return 0;
        }

        //先获取文章列表，建立全部目录
        $url = config('app.url').'/api/v2/article-map';

        $this->info('打开csv文件并读取数据');
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
        //查询文集语言
        $srcAnthology = Collection::where('uid',$anthologyId)->first();
        if(!$srcAnthology){
            $this->error("文集不存在 anthologyId=".$anthologyId);
            return 0;
        }
        $lang = $srcAnthology->lang;
        if(empty($lang)){
            $this->error("文集语言不能为空 anthologyId=".$anthologyId);
            return 0;
        }
        $inputRow = 0;
        $currSize=0;
        $currBlock=1;
        $currDir='';
        $success = 0;
        $fail = 0;
        $articleMap = array();
        while (($data = fgetcsv($fp, 0, ',')) !== false) {
            if($inputRow>0){
                $id = $data[0];
                $dir = $data[1];
                $title = $data[2];
                $realTitle = "[{$id}]{$title}";
                $realTitle = mb_substr($realTitle,0,128,'UTF-8');
                $reference = $data[5];

                $percent = (int)($inputRow*100/6984);
                $this->info("[{$percent}%] doing ".$realTitle);

                if($this->option('size')){
                    $currDir = $srcAnthology->title . '-' . $currBlock;
                    if($currSize > $this->option('size')){
                        $currBlock++;
                        $currSize=0;
                    }
                }else{
                    $currDir = $dir;
                }
                //查找目录文章是否存在
                $dirArticle = Article::where('owner',$studioId)
                              ->where('title',$currDir)
                              ->first();
                if($dirArticle){
                    $dirId = $dirArticle->uid;
                }else{
                    $this->info('不存在目录'.$currDir.'新建');
                    $url = config('app.url').'/api/v2/article';
                    sleep(2);
                    $response = Http::withToken($token)->post($url,
                    [
                        'title'=> $currDir,
                        'lang'=> $lang,
                        'studio'=> $studioName,
                        'anthologyId'=> $anthologyId,
                    ]);
                    if($response->ok()){
                        $this->info('dir create ok title='.$currDir);
                        $dirId = $response->json('data.uid');
                    }else{
                        $this->error('create dir fail.'.$currDir);
                        Log::error('create dir fail title='.$currDir);
                        $fail++;
                        continue;
                    }
                }
                //创建目录结束
                if(!isset($articleMap[$dirId])){
                    $articleMap[$dirId] = ['name'=>$currDir,'children'=>[]];
                }
                //查找文章
                $article = Article::where('owner',$srcStudioId)
                              ->where('title',$realTitle)
                              ->first();
                if(!$article){
                    $this->error('文章没找到.'.$realTitle);
                    Log::error('文章没找到 title='.$realTitle);
                    $fail++;
                    continue;
                }
                $articleMap[$dirId]['children'][] = [
                    'id'=>$article->uid,
                    'title'=>$article->title,
                ];
                if($this->option('size')){
                    $currSize += mb_strlen($title,'UTF-8') +
                                mb_strlen($data[4],'UTF-8') +
                                mb_strlen($reference,'UTF-8');
                }
                $success++;
            }
            $inputRow++;
        }
        $this->info("找到文章=" .$success);
        $this->info("目录=" .count($articleMap));

        $this->info('正在准备map数据');

        $data = array();
        foreach ($articleMap as $dirId => $dir) {
            $data[] = [
                    'article_id'=> $dirId,
                    'level'=> 1,
                    'title'=> $dir['name'],
                    'children'=> count($dir['children']),
                    'deleted_at'=> null,
            ];
            foreach ($dir['children'] as $key => $child) {
                $data[] = [
                        'article_id'=> $child['id'],
                        'level'=> 2,
                        'title'=> $child['title'],
                        'children'=> 0,
                        'deleted_at'=> null,
                ];
            }
        }
        $this->info('map data='.count($data));

        //目录写入db
        $url = config('app.url').'/api/v2/article-map/'.$anthologyId;
        $response = Http::withToken($token)->put($url,
        [
            'data'=> $data,
            'operation' => "anthology",
        ]);
        if($response->ok()){
            $this->info('map update ok ');
        }else{
            $this->error('map update  fail.');
            Log::error('map update  fail ');
        }
        return 0;
    }
}
