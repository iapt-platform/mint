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
     * php artisan export:article 4732bcae-fb9d-4db4-b6b7-e8d0aa882f30 1234 --channel=7fea264d-7a26-40f8-bef7-bc95102760fb --anthology=eb9e3f7f-b942-4ca4-bd6f-b7876b59a523 --format=html
     * @var string
     */
    protected $signature = 'export:article {id} {filename} {--anthology=} {--channel=}  {--origin=false} {--translation=true} {--format=tex} {--debug}';

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
        $upload = new ExportDownload([
            'filename'=>$this->argument('filename').'.zip',
            'format'=>$this->option('format'),
            'debug'=>$this->option('debug'),
            'real_filename'=>'article',
        ]);

        MdRender::init();
        $m = new \Mustache_Engine(array('entity_flags'=>ENT_QUOTES,
                                        'delimiters' => '[[ ]]',
                                        'escape'=>function ($value){
                                            return $value;
                                        }));

        $api = 'http://127.0.0.1:8000/api';
        $basicUrl = $api . '/v2/article/';
        $sections = array();
        $articles = array();

        $url =  $basicUrl . $this->argument('id');
        $this->info('http request url='.$url);
        $response = Http::get($url,[
                'mode' => 'read',
                'format' => 'html',
                'anthology'=> $this->option('anthology'),
                'channel' => $this->option('channel'),
            ]);
        if($response->failed()){
            $this->error('http request error');
        }
        if(!$response->json('ok')){
            $this->error('http request error'.$response->json('message'));
        }
        $article = $response->json('data');
        $bookMeta = array();
        $bookMeta['book_author'] = "";
        $bookMeta['book_title'] = $article['title_text'];

        $articles[] = [
            'level'=>1,
            'title'=>$article['title_text'],
            'content'=>isset($article['html'])?$article['html']:'',
        ];
        if(isset($article['toc'])){
            $this->info('has sub article '. count($article['toc']));
        }
        foreach ($article['toc'] as $key => $value) {
            $url =  $basicUrl . $value['key'];
            $this->info('http request url='.$url);
            $response = Http::get($url,[
                    'mode' => 'read',
                    'format' => 'html',
                    'anthology'=> $this->option('anthology'),
                    'channel' => $this->option('channel'),
                ]);
            if($response->failed()){
                $this->error('http request error');
                $this->error('http request error'.$response->json('message'));
                continue;
            }
            if(!$response->json('ok')){
                $this->error('http request error'.$response->json('message'));
                continue;
            }
            $article = $response->json('data');
            $this->info('sub article title='.$article['title_text']);
            $articles[] = [
                'level'=>$value['level'],
                'title'=>$article['title_text'],
                'content'=>isset($article['html'])?$article['html']:'',
            ];
        }
        $sections[] = [
            'name'=>'first',
            'body'=>['articles'=>$articles],
        ];
        $this->info($upload->setStatus(0.9,'export content done'));
        Log::debug('导出结束');


        $upload->upload('article',$sections,$bookMeta);

        return 0;
    }
}
