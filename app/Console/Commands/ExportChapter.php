<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\ProgressChapter;
use App\Models\Channel;
use App\Models\PaliText;
use App\Models\Sentence;
use App\Http\Api\ChannelApi;
use App\Http\Api\MdRender;
use App\Tools\Export;
use Illuminate\Support\Facades\Log;
use App\Tools\RedisClusters;

class ExportChapter extends Command
{
    protected $exportStatusKey = 'export/status';
    protected $exportStatusExpiry = 3600;
    protected $currExportStatusKey = '';
    /**
     * The name and signature of the console command.
     * php artisan export:chapter 213 1913 a19eaf75-c63f-4b84-8125-1bce18311e23 213-1913 --format=html
     * php artisan export:chapter 168 915 19f53a65-81db-4b7d-8144-ac33f1217d34 168-915 --format=html
     * @var string
     */
    protected $signature = 'export:chapter {book} {para} {channel} {filename} {--debug} {--format=tex} ';

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
     * progress: 0-1, error -1
     * message: string
     */
    protected function setStatus($progress,$message=''){
        RedisClusters::put($this->currExportStatusKey,
                            [
                                'progress'=>$progress,
                                'message'=>$message,
                            ],
                            $this->exportStatusExpiry);
    }
    public function getStatus($filename){
        return RedisClusters::get($this->exportStatusKey.'/'.$filename);
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

        MdRender::init();

        $this->currExportStatusKey = $this->exportStatusKey . '/' . $this->argument('filename');

        switch ($this->option('format')) {
            case 'md':
                $renderFormat='markdown';
                break;
            case 'html':
                $renderFormat='html';
                break;
            default:
                $renderFormat=$this->option('format');
                break;
        }
        $book = $this->argument('book');
        $para = $this->argument('para');
        $channelId = $this->argument('channel');
        $channel = ChannelApi::getById($channelId);
        $chapter = PaliText::where('book',$book)->where('paragraph',$para)->first();
        if(!$chapter){
            return $this->error("no data");
        }

        $currProgress = 0;
        $this->setStatus($currProgress,'start');

        $bookMeta = array();
        if(empty($chapter->toc)){
            $bookMeta['title'] = "unknown";
        }else{
            $title = ProgressChapter::where('book',$book)->where('para',$para)
                                    ->where('channel_id',$channelId)
                                    ->value('title');
            $bookMeta['book_title'] = $title?$title:$chapter->toc;
            $bookMeta['sub_title'] = $chapter->toc;
        }
        if($channel){
            $bookMeta['book_author'] = $channel['name'];
        }

        $subChapter = PaliText::where('book',$book)->where('parent',$para)
                              ->where('level','<',8)
                              ->orderBy('paragraph')
                              ->get();
        if(count($subChapter) === 0){
            //没有子章节
            $subChapter = PaliText::where('book',$book)->where('paragraph',$para)
                              ->where('level','<',8)
                              ->orderBy('paragraph')
                              ->get();
        }


        $step = 1 / PaliText::where('book',$book)->where('paragraph',$para)->value('chapter_len');

        $sections = array();
        foreach ($subChapter as $key => $sub) {
            # code...
            $chapter = ProgressChapter::where('book',$book)->where('para',$sub->paragraph)
                                    ->where('channel_id',$channelId)
                                    ->first();
            if($chapter){
                $filename = "{$sub->paragraph}.".$this->option('format');
                $bookMeta['sections'][] = ['filename'=>$filename];
                $paliTitle = PaliText::where('book',$book)->where('paragraph',$sub->paragraph)->value('toc');
                $title = $chapter->title?$chapter->title:$paliTitle;
                $content = array();

                $chapterStart = $sub->paragraph+1;
                $chapterEnd = $sub->paragraph + $sub->chapter_len;
                $chapterBody = PaliText::where('book',$book)
                                          ->whereBetween('paragraph',[$chapterStart,$chapterEnd])
                                          ->orderBy('paragraph')->get();
                foreach ($chapterBody as $body) {
                    $currProgress += $step;
                    $this->setStatus($currProgress,'export chapter '.$body->paragraph);
                    # code...
                    $translationData = Sentence::where('book_id',$book)
                                    ->where('paragraph',$body->paragraph)
                                    ->where('channel_uid',$channelId)
                                    ->orderBy('word_start')->get();
                    $sentContent = array();
                    foreach ($translationData as $sent) {
                        $texText = MdRender::render($sent->content,
                                                        [$sent->channel_uid],
                                                        null,
                                                        'read',
                                                        $channel['type'],
                                                        $sent->content_type,
                                                        $renderFormat
                                                        );
                        $sentContent[] = trim($texText);
                    }
                    $paraContent = implode(' ',$sentContent);
                    if($body->level > 7){
                        switch ($this->option('format')) {
                            case 'tex':
                                $content[] = '\par '.$paraContent;
                                break;
                            case 'html':
                                $content[] = '<p>'.$paraContent.'</p>';
                                break;
                            case 'md':
                                $content[] = "\n\n".$paraContent;
                                break;
                        }

                    }else{
                        $currLevel = $body->level - $sub->level;
                        if($currLevel>0){
                            if(empty($paraContent)){
                                $subSessionTitle = PaliText::where('book',$book)
                                                     ->where('paragraph',$body->paragraph)
                                                     ->value('toc');
                            }else{
                                $subSessionTitle = $paraContent;
                            }
                            switch ($this->option('format')) {
                                case 'tex':
                                    $subStr = array_fill(0,$currLevel,'sub');
                                    $content[] = '\\'. implode('',$subStr) . "section{".$subSessionTitle.'}';
                                    break;
                                case 'md':
                                    $subStr = array_fill(0,$currLevel,'#');
                                    $content[] = implode('',$subStr) . " ".$subSessionTitle;
                                    break;
                                case 'html':
                                    $level = $currLevel+2;
                                    $content[] = "<h{$currLevel}".$subSessionTitle."</h{$currLevel}";
                                    break;
                            }

                        }else{
                            $content[] = '\par '.$paraContent;
                        }
                    }
                    $content[] = "\n\n";
                }

                $sections[] = [
                                'name'=>$filename,
                                'body'=>[
                                    'title'=>$title,
                                    'content'=>implode('',$content)
                                ]
                            ];
            }
        }

        $this->setStatus(0.9,'export content done');
        Log::debug('导出结束');

        $tex = array();
        $m = new \Mustache_Engine(array('entity_flags'=>ENT_QUOTES,
                                        'delimiters' => '[[ ]]',
                                        'escape'=>function ($value){
                                            return $value;
                                        }));
        $tpl = file_get_contents(resource_path("mustache/".$this->option('format')."/main.".$this->option('format')));
        $texContent = $m->render($tpl,$bookMeta);
        $tex[] = ['name'=>'main.'.$this->option('format'),
                  'content'=>$texContent
                 ];
        foreach ($sections as $key => $section) {
            $tpl = file_get_contents(resource_path("mustache/".$this->option('format')."/section.".$this->option('format')));
            $texContent = $m->render($tpl,$section['body']);
            $tex[] = ['name'=>$section['name'],
                  'content'=>$texContent
                 ];
        }

        Log::debug('footnote start');
        //footnote
        $tplFile = resource_path("mustache/".$this->option('format')."/footnote.".$this->option('format'));
        if(isset($GLOBALS['note']) &&
            is_array($GLOBALS['note']) &&
            count($GLOBALS['note'])>0 &&
            file_exists($tplFile)){
            $tpl = file_get_contents($tplFile);
            $texContent = $m->render($tpl,['footnote'=>$GLOBALS['note']]);
            $tex[] = ['name'=>'footnote.'.$this->option('format'),
                        'content'=>$texContent
                        ];
        }
        if($this->option('debug')){
            $dir = "export/".$this->option('format')."/{$book}-{$para}-{$channelId}/";
            foreach ($tex as $key => $section) {
                Storage::disk('local')->put($dir.$section['name'], $section['content']);
            }
        }

        Log::debug('footnote finished');
        Log::debug('upload start');
        $this->setStatus(0.95,'export content done');

        //upload
        switch ($this->option('format')) {
            case 'tex':
                $data = Export::ToPdf($tex);
                if($data['ok']){
                    $filename = "export/".$this->argument('filename');
                    $this->info($data['content-type']);
                    Storage::put($filename, $data['data']);
                }else{
                    $this->error($data['code'].'-'.$data['message']);
                }
                break;
            case 'html':
                $filename = "export/".$this->argument('filename');
                $file = array();
                foreach ($tex as $key => $section) {
                    $file[] = $section['content'];
                }
                Storage::put($filename, implode('',$file));
                break;
        }
        $this->setStatus(1,'export done');
        Log::debug('task export offline chapter-table finished');
        return 0;
    }
}
