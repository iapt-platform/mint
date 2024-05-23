<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

use App\Models\ProgressChapter;
use App\Models\Channel;
use App\Models\PaliText;
use App\Models\Sentence;

use App\Http\Api\ChannelApi;
use App\Http\Api\MdRender;
use App\Tools\Export;
use App\Tools\RedisClusters;
use App\Tools\ExportDownload;


class ExportChapter extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan export:chapter 213 3 a19eaf75-c63f-4b84-8125-1bce18311e23 213-3.html --format=html --origin=true
     * php artisan export:chapter 168 915 7fea264d-7a26-40f8-bef7-bc95102760fb 168-915.html --format=html --debug
     * php artisan export:chapter 168 915 7fea264d-7a26-40f8-bef7-bc95102760fb 168-915.html --format=html --origin=true
     * @var string
     */
    protected $signature = 'export:chapter {book} {para} {channel} {query_id} {--token=} {--origin=false} {--translation=true} {--debug} {--format=tex} ';

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
        $book = $this->argument('book');
        $para = $this->argument('para');

        $upload = new ExportDownload([
            'queryId'=>$this->argument('query_id'),
            'format'=>$this->option('format'),
            'debug'=>$this->option('debug'),
            'filename'=>$book.'-'.$para,
        ]);

        $m = new \Mustache_Engine(array('entity_flags'=>ENT_QUOTES,
                                        'delimiters' => '[[ ]]',
                                        'escape'=>function ($value){
                                            return $value;
                                        }));
        $tplFile = resource_path("mustache/chapter/".$this->option('format')."/paragraph.".$this->option('format'));
        $tplParagraph = file_get_contents($tplFile);

        MdRender::init();


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

        //获取原文channel
        $orgChannelId = ChannelApi::getSysChannel('_System_Pali_VRI_');

        $tranChannelsId = explode('_',$this->argument('channel'));

        $channelsId = array_merge([$orgChannelId],$tranChannelsId);

        $channels = array();
        $channelsIndex = array();
        foreach ($channelsId as $key => $id) {
            $channels[] = ChannelApi::getById($id);
            $channelsIndex[$id] = ChannelApi::getById($id);
        }

        $bookMeta = array();
        $bookMeta['book_author'] = "";
        foreach ($channels as $key => $channel) {
            $bookMeta['book_author'] .= $channel['name'] . ' ';
        }

        $chapter = PaliText::where('book',$book)
                           ->where('paragraph',$para)->first();
        if(!$chapter){
            return $this->error("no data");
        }

        $currProgress = 0;
        $this->info($upload->setStatus($currProgress,'start'));


        if(empty($chapter->toc)){
            $bookMeta['title'] = "unknown";
        }else{
            $bookMeta['book_title'] = '';
            foreach ($channelsId as $key => $id) {
                $title = ProgressChapter::where('book',$book)->where('para',$para)
                        ->where('channel_id',$id)
                        ->value('title');
                $bookMeta['book_title'] .= $title;
            }
            $bookMeta['sub_title'] = $chapter->toc;
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

        $chapterParagraph = PaliText::where('book',$book)->where('paragraph',$para)->value('chapter_len');
        if($chapterParagraph >0 ){
            $step = 0.9 / $chapterParagraph;
        }else{
            $step = 0.9;
            Log::error('段落长度不能为0',['book'=>$book,'para'=>$para]);
        }

        $outputChannelsId = [];
        if($this->option('origin') === 'true'){
            $outputChannelsId[] = $orgChannelId;
        }
        if($this->option('translation') === 'true'){
            $outputChannelsId = array_merge($outputChannelsId,$tranChannelsId);
        }

        $sections = array();
        foreach ($subChapter as $key => $sub) {
            # 看这个章节是否存在译文
            $hasChapter = false;
            if($this->option('origin') === 'true'){
                $hasChapter = true;
            }
            if($this->option('translation') === 'true'){
                foreach ($tranChannelsId as $id) {
                    if(ProgressChapter::where('book',$book)->where('para',$sub->paragraph)
                        ->where('channel_id',$id)
                        ->exists()){
                            $hasChapter = true;
                    }
                }
            }
            if(!$hasChapter){
                //不存在需要导出的数据
                continue;
            }
            $filename = "{$sub->paragraph}.".$this->option('format');
            $bookMeta['sections'][] = ['filename'=>$filename];
            $paliTitle = PaliText::where('book',$book)
                                 ->where('paragraph',$sub->paragraph)
                                 ->value('toc');
            $sectionTitle = $paliTitle;
            if($this->option('translation') === 'true'){
                $chapter = ProgressChapter::where('book',$book)->where('para',$sub->paragraph)
                                        ->where('channel_id',$tranChannelsId[0])
                                        ->first();
                if($chapter && !empty($chapter->title)){
                    $sectionTitle = $chapter->title;
                }
            }


            $content = array();

            $chapterStart = $sub->paragraph+1;
            $chapterEnd = $sub->paragraph + $sub->chapter_len;
            $chapterBody = PaliText::where('book',$book)
                                    ->whereBetween('paragraph',[$chapterStart,$chapterEnd])
                                    ->orderBy('paragraph')->get();



            foreach ($chapterBody as $body) {
                $currProgress += $step;
                $this->info($upload->setStatus($currProgress,'export chapter '.$body->paragraph));
                $paraData = array();
                $paraData['translations'] = array();
                foreach ($outputChannelsId as $key => $channelId) {
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
                                                    $channelsIndex[$channelId]['type'],
                                                    $sent->content_type,
                                                    $renderFormat
                                                    );
                        $sentContent[] = trim($texText);
                    }
                    $paraContent = implode(' ',$sentContent);
                    if($channelsIndex[$channelId]['type'] === 'original'){
                        $paraData['origin'] = $paraContent;
                    }else{
                        $paraData['translations'][] = ['content'=>$paraContent];
                    }
                }
                if($body->level > 7){
                    $content[] = $m->render($tplParagraph,$paraData);
                }else{
                    $currLevel = $body->level - $sub->level;
                    if($currLevel<=0){
                        $currLevel = 1;
                    }

                    if(count($paraData['translations'])===0){
                        $subSessionTitle = PaliText::where('book',$book)
                                            ->where('paragraph',$body->paragraph)
                                            ->value('toc');
                    }else{
                        $subSessionTitle = $paraData['translations'][0]['content'];
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
                            $content[] = "<h{$currLevel}>".$subSessionTitle."</h{$currLevel}>";
                            break;
                    }
                }
                $content[] = "\n\n";
            }

            $sections[] = [
                    'name'=>$filename,
                    'body'=>[
                        'title'=>$sectionTitle,
                        'content'=>implode('',$content)
                    ]
                ];
        }

        //导出术语表
        $keyPali = array();
        $keyMeaning = array();
        if(isset($GLOBALS['glossary'])){
            $glossary = $GLOBALS['glossary'];
            foreach ($glossary as $word => $meaning) {
                $keyMeaning[$meaning] = $word;
                $keyPali[$word] = $meaning;
            }
        }

        ksort($keyPali);
        krsort($keyMeaning);
        $glossaryData = [];
        $glossaryData['pali'] = [];
        $glossaryData['meaning'] = [];
        foreach ($keyPali as $word => $meaning) {
            $glossaryData['pali'][] = ['pali'=>$word,'meaning'=>$meaning];
        }
        foreach ($keyMeaning as $meaning => $word) {
            $glossaryData['meaning'][] = ['pali' => $word,'meaning'=>$meaning];
        }

        Log::debug('glossary',['data' => $glossaryData]);

        $tplFile = resource_path("mustache/chapter/".$this->option('format')."/glossary.".$this->option('format'));
        $tplGlossary = file_get_contents($tplFile);

        $glossaryContent = $m->render($tplGlossary,$glossaryData);

        $sections[] = [
            'name'=>'glossary.'.$this->option('format'),
            'body'=>[
                'title' => 'glossary',
                'content' => $glossaryContent
            ]
        ];

        $this->info($upload->setStatus(0.9,'export content done'));

        Log::debug('导出结束',['sections'=>count($sections)]);

        $upload->upload('chapter',$sections,$bookMeta);
        $this->info($upload->setStatus(1,'export chapter done'));

        return 0;
    }
}
