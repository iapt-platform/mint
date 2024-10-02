<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

use App\Tools\RedisClusters;
use App\Tools\ExportDownload;
use App\Http\Api\MdRender;


class ExportArticle extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan export:article 78c22ad3-58e2-4cf0-b979-67783ca3a375 123 --channel=7fea264d-7a26-40f8-bef7-bc95102760fb --format=html
     * php artisan export:article df6c6609-6fc1-42d0-9ef1-535ef3e702c9 1234 --origin=true --channel=7fea264d-7a26-40f8-bef7-bc95102760fb  --format=docx --anthology=697c9169-cb9d-4a60-8848-92745e467bab --token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJuYmYiOjE2OTc3Mjg2ODUsImV4cCI6MTcyOTI2NDY4NSwidWlkIjoiYmE1NDYzZjMtNzJkMS00NDEwLTg1OGUtZWFkZDEwODg0NzEzIiwiaWQiOjR9.fiXhnY2LczZ9kKVHV0FfD3AJPZt-uqM5wrDe4EhToVexdd007ebPFYssZefmchfL0mx9nF0rgHSqjNhx4P0yDA
     * @var string
     */
    protected $signature = 'export:article {id} {query_id} {--token=} {--anthology=} {--channel=}  {--origin=false} {--translation=true} {--format=tex} {--debug}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $this->info('task export chapter start');
        Log::debug('task export chapter start');
        if(\App\Tools\Tools::isStop()){
            return 0;
        }
        $options = [
            'queryId'=>$this->argument('query_id'),
            'format'=>$this->option('format'),
            'debug'=>$this->option('debug'),
            'filename'=>'article',
        ];
        $upload = new ExportDownload($options);

        MdRender::init();
        $m = new \Mustache_Engine(array('entity_flags'=>ENT_QUOTES,
                                        'delimiters' => '[[ ]]',
                                        'escape'=>function ($value){
                                            return $value;
                                        }));

        $sections = array();
        $articles = array();


        $article = $this->fetch($this->argument('id'));
        if(!$article){
            return 1;
        }

        $bookMeta = array();
        $bookMeta['book_author'] = "";
        $bookMeta['book_title'] = $article['title_text'];

        $articles[] = [
            'level'=>1,
            'title'=>$article['title_text'],
            'content'=>isset($article['html'])?$article['html']:'',
        ];
        $progress = 0.1;
        $this->info($upload->setStatus($progress,'export article content title='.$article['title_text']));

        if(isset($article['toc']) && count($article['toc'])>0){
            $this->info('has sub article '. count($article['toc']));
            $step = 0.8 / count($article['toc']);
            $baseLevel = 0;
            foreach ($article['toc'] as $key => $value) {
                if($baseLevel === 0){
                    $baseLevel = $value['level'] - 2;
                }
                $progress += $step;
                $this->info($upload->setStatus($progress,'exporting article title='.$value['title']));
                $article = $this->fetch($value['key']);
                if(!$article){
                    $this->info($upload->setStatus($progress,'exporting article fail title='.$value['title']));
                    continue;
                }
                $this->info($upload->setStatus($progress,'exporting article success title='.$article['title_text']));
                $articles[] = [
                    'level'=>$value['level']-$baseLevel,
                    'title'=>$article['title_text'],
                    'content'=>isset($article['html'])?$article['html']:'',
                ];
            }
        }

        $sections[] = [
            'name'=>'articles',
            'body'=>['articles'=>$articles],
        ];
        $this->info($upload->setStatus(0.9,'export article content done'));
        Log::debug('导出结束');


        $upload->upload('article',$sections,$bookMeta);
        $this->info($upload->setStatus(1,'export article done'));
        return 0;
    }

    private function fetch($articleId){
        $api = config('mint.server.api.bamboo');
        $basicUrl = $api . '/v2/article/';
        $url =  $basicUrl . $articleId;;
        $this->info('http request url='.$url);

        $urlParam = [
                'mode' => 'read',
                'format' => 'markdown',
                'anthology'=> $this->option('anthology'),
                'channel' => $this->option('channel'),
                'origin' => 'true' /*$this->option('origin')*/,
                'paragraph' => true,
        ];

        Log::debug('export article http request',['url'=>$url,'param'=>$urlParam]);
        if($this->option('token')){
            $response = Http::withToken($this->option('token'))->get($url,$urlParam);
        }else{
            $response = Http::get($url,$urlParam);
        }

        if($response->failed()){
            $this->error('http request error'.$response->json('message'));
            Log::error('http request error',['error'=>$response->json('message')]);
            return false;
        }
        if(!$response->json('ok')){
            $this->error('http request error'.$response->json('message'));
            return false;
        }
        $article = $response->json('data');
        return $article;
    }
}
