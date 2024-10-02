<?php
namespace App\Http\Api;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

use App\Models\DhammaTerm;
use App\Models\PaliText;
use App\Models\Channel;
use App\Models\PageNumber;
use App\Models\Discussion;
use App\Models\BookTitle as BookSeries;

use App\Http\Controllers\CorpusController;

use App\Tools\RedisClusters;
use App\Http\Api\ChannelApi;
use App\Http\Api\MdRender;
use App\Http\Api\PaliTextApi;

class TemplateRender{
    protected $param = [];
    protected $mode = "read";
    protected $channel_id = [];
    protected $debug = [];
    protected $format = 'react';
    protected $studioId = null;
    protected $lang = 'en';
    protected $langFamily = 'en';

    protected $options = [
        'mode' => 'read',
        'channelType'=>'translation',
        'contentType'=>"markdown",
        'format'=>'react',
        'debug'=>[],
        'studioId'=>null,
        'lang'=>'zh-Hans',
        'footnote'=>false,
        'paragraph'=>false,
        'origin'=>true,
        'translation'=>true,
        ];

    /**
     * Create a new command instance.
     * string $mode  'read' | 'edit'
     * string $format  'react' | 'text' | 'tex' | 'unity'
     * @return void
     */
    public function __construct($param, $channelInfo, $mode,$format='react',$studioId='',$debug=[],$lang='zh-Hans')
    {
        $this->param = $param;
        foreach ($channelInfo as $value) {
            $this->channel_id[] = $value->uid;
        }
        $this->channelInfo = $channelInfo;
        $this->mode = $mode;
        $this->format = $format;
        $this->studioId = $studioId;
        $this->debug = $debug;
        $this->glossaryKey = 'glossary';

        if(count($this->channel_id)>0){
            $channelId = $this->channel_id[0];
            if(Str::isUuid($channelId)){
                $lang = Channel::where('uid',$channelId)->value('lang');
            }
        }
        if(!empty($lang)){
            $this->lang = $lang;
            $this->langFamily = explode('-',$lang)[0];
        }
    }
    public function options($options=[]){
        foreach ($options as $key => $value) {
            $this->options[$key] = $value;
        }
    }
    public function glossaryKey(){
        return $this->glossaryKey;
    }
    /**
     * TODO 设置默认语言。在渲染某些内容的时候需要语言信息
     */
    public function setLang($lang){
        $this->lang = $lang;
        $this->langFamily = explode('-',$lang)[0];
    }
    private function info($message,$debug){
        if(in_array($debug,$this->debug)){
            Log::info($message);
        }
    }
    private function error($message,$debug){
        if(in_array($debug,$this->debug)){
            Log::error($message);
        }
    }
    public function render($tpl_name){
        switch ($tpl_name) {
            case 'term':
                # 术语
                $result = $this->render_term();
                break;
            case 'note':
                $result = $this->render_note();
                break;
            case 'sent':
                $result = $this->render_sent();
                break;
            case 'quote':
                $result = $this->render_quote();
                break;
            case 'ql':
                $result = $this->render_quote_link();
                break;
            case 'exercise':
                $result = $this->render_exercise();
                break;
            case 'article':
                $result = $this->render_article();
                break;
            case 'nissaya':
                $result = $this->render_nissaya();
                break;
            case 'mermaid':
                $result = $this->render_mermaid();
                break;
            case 'qa':
                $result = $this->render_qa();
                break;
            case 'v':
                $result = $this->render_video();
                break;
            case 'g':
                $result = $this->render_grammar_lookup();
                break;
            case 'ref':
                $result = $this->render_ref();
                break;
            default:
                # code...
                $result = [
                    'props'=>base64_encode(\json_encode([])),
                    'html'=>'',
                    'tag'=>'span',
                    'tpl'=>'unknown',
                ];
                break;
        }
        return $result;
    }

    public function getTermProps($word,$tag=null,$channel=null){
        if($channel && !empty($channel)){
            $channelId = $channel;
        }else{
            if(count($this->channel_id)>0){
                $channelId = $this->channel_id[0];
            }else{
                $channelId = null;
            }
        }

        if(count($this->channelInfo)===0){
            if(!empty($channel)){
                $channelInfo = Channel::where('uid',$channel)->first();
                if(!$channelInfo){
                    unset($channelInfo);
                }
            }
            if(!isset($channelInfo)){
                Log::error('channel is null');
                $output = [
                    "word" => $word,
                    'innerHtml' => '',
                ];
                return $output;
            }
        }else{
            $channelInfo = $this->channelInfo[0];
        }

        if(Str::isUuid($channelId)){
            $lang = Channel::where('uid',$channelId)->value('lang');
            if(!empty($lang)){
                $langFamily = explode('-',$lang)[0];
            }else{
                $langFamily = 'zh';
            }
            $this->info("term:{$word} 先查属于这个channel 的",'term');
            $this->info('channel id'.$channelId,'term');
            $table = DhammaTerm::where("word",$word)
                                ->where('channal',$channelId);
            if($tag && !empty($tag)){
                $table = $table->where('tag',$tag);
            }
            $tplParam = $table->orderBy('updated_at','desc')
                                ->first();
            $studioId = $channelInfo->owner_uid;
        }else{
            $tplParam = false;
            $lang = '';
            $langFamily = '';
            $studioId = $this->studioId;
        }

        if(!$tplParam){
            if(Str::isUuid($studioId)){
            /**
             * 没有，再查这个studio的
             * 按照语言过滤
             * 完全匹配的优先
             * 语族匹配也行
             */
                $this->info("没有-再查这个studio的",'term');
                $table = DhammaTerm::where("word",$word);
                if(!empty($tag)){
                    $table = $table->where('tag',$tag);
                }
                $termsInStudio = $table->where('owner',$channelInfo->owner_uid)
                                    ->orderBy('updated_at','desc')
                                    ->get();
                if(count($termsInStudio)>0){
                    $list = array();
                    foreach ($termsInStudio as $key=>$term) {
                        if(empty($term->channal)){
                            if($term->language===$lang){
                                $list[$term->guid]=2;
                            }else if(strpos($term->language,$langFamily) !== false){
                                $list[$term->guid]=1;
                            }
                        }
                    }
                    if(count($list)>0){
                        arsort($list);
                        foreach ($list as $key => $one) {
                            foreach ($termsInStudio as $term) {
                                if($term->guid===$key){
                                    $tplParam = $term;
                                    break;
                                }
                            }
                            break;
                        }
                    }
                }
            }
        }

        if(!$tplParam){
            $this->info("没有，再查社区",'term');
            $community_channel = ChannelApi::getSysChannel("_community_term_zh-hans_");
            $table = DhammaTerm::where("word",$word);
            if(!empty($tag)){
                $table = $table->where('tag',$tag);
            }
            $tplParam = $table->where('channal',$community_channel)
                                  ->first();
            if($tplParam){
                $isCommunity = true;
            }else{
                $this->info("查社区没有",'term');
            }
        }
        $output = [
            "word" => $word,
            "parentChannelId" => $channelId,
            "parentStudioId" => $channelInfo->owner_uid,
            ];
        $innerString = $output["word"];
        if($tplParam){
            $output["id"] = $tplParam->guid;
            $output["meaning"] = $tplParam->meaning;
            $output["channel"] = $tplParam->channal;
            if(!empty($tplParam->note)){
                $mdRender = new MdRender(['format'=>$this->format]);
                $output['note'] = $mdRender->convert($tplParam->note,$this->channel_id);
            }
            if(isset($isCommunity)){
                $output["isCommunity"] = true;
            }
            $innerString = "{$output["meaning"]}({$output["word"]})";
            if(!empty($tplParam->other_meaning)){
                $output["meaning2"] = $tplParam->other_meaning;
            }
        }
        $output['innerHtml'] = $innerString;
        return $output;
    }

    private function render_term(){
        $word = $this->get_param($this->param,"word",1);

        $props = $this->getTermProps($word,'');

        $output = $props['word'];
        switch ($this->format) {
            case 'react':
                $output=[
                    'props'=>base64_encode(\json_encode($props)),
                    'html'=>$props['innerHtml'],
                    'tag'=>'span',
                    'tpl'=>'term',
                    ];
                break;
            case 'unity':
                $output=[
                    'props'=>base64_encode(\json_encode($props)),
                    'tpl'=>'term',
                    ];
                break;
            case 'html':
                if(isset($props["meaning"])){
                    $GLOBALS[$this->glossaryKey][$props["word"]] = $props['meaning'];

                    $key = 'term-'.$props["word"];
                    $termHead = "<a href='#'>".$props['meaning']."</a>";

                    if(isset($GLOBALS[$key])){
                        $output = $termHead;
                    }else{
                        $GLOBALS[$key] = 1;
                        $output = $termHead.'(<em>'.$props["word"].'</em>)';
                    }
                }else{
                    $output = $props["word"];
                }
                break;
            case 'text':
                if(isset($props["meaning"])){
                    $key = 'term-'.$props["word"];
                    if(isset($GLOBALS[$key])){
                        $output = $props["meaning"];
                    }else{
                        $GLOBALS[$key] = 1;
                        $output = $props["meaning"].'('.$props["word"].')';
                    }
                }else{
                    $output = $props["word"];
                }
                break;
            case 'tex':
                if(isset($props["meaning"])){
                    $key = 'term-'.$props["word"];
                    if(isset($GLOBALS[$key])){
                        $output = $props["meaning"];
                    }else{
                        $GLOBALS[$key] = 1;
                        $output = $props["meaning"].'('.$props["word"].')';
                    }
                }else{
                    $output = $props["word"];
                }
                break;
            case 'simple':
                if(isset($props["meaning"])){
                    $output = $props["meaning"];
                }else{
                    $output = $props["word"];
                }
                break;
            case 'markdown':
                if(isset($props["meaning"])){
                    $key = 'term-'.$props["word"];
                    if(isset($GLOBALS[$key]) && $GLOBALS[$key]===1){
                        $GLOBALS[$key]++;
                        $output = $props["meaning"];
                    }else{
                        $GLOBALS[$key] = 1;
                        $output = $props["meaning"].'('.$props["word"].')';
                    }
                }else{
                    $output = $props["word"];
                }
                //如果有内容且第一次出现，显示为脚注
                if(!empty($props["note"]) && $GLOBALS[$key]===1){
                    if(isset($GLOBALS['note_sn'])){
                        $GLOBALS['note_sn']++;
                    }else{
                        $GLOBALS['note_sn'] = 1;
                        $GLOBALS['note'] = array();
                    }
                    $content = $props["note"];
                    $output .= '[^'.$GLOBALS['note_sn'].']';
                    $GLOBALS['note'][] = [
                        'sn' => $GLOBALS['note_sn'],
                        'trigger' => '',
                        'content' => $content,
                        ];
                }
                break;
            default:
                if(isset($props["meaning"])){
                    $output = $props["meaning"];
                }else{
                    $output = $props["word"];
                }
                break;
        }
        return $output;
    }

    private  function render_note(){
        $note = $this->get_param($this->param,"text",1);
        $trigger = $this->get_param($this->param,"trigger",2);
        $props = ["note" => $note ];
        $innerString = "";
        if(!empty($trigger)){
            $props["trigger"] = $trigger;
            $innerString = $props["trigger"];
        }
        if($this->format==='unity'){
            $props["note"] = MdRender::render(
                $props["note"],
                $this->channel_id,
                null,
                'read',
                'translation',
                'markdown',
                'unity'
                );
        }
        $output = $note;
        switch ($this->format) {
            case 'react':
                $output=[
                    'props'=>base64_encode(\json_encode($props)),
                    'html'=>$innerString,
                    'tag'=>'span',
                    'tpl'=>'note',
                    ];
                break;
            case 'unity':
                $output=[
                    'props'=>base64_encode(\json_encode($props)),
                    'tpl'=>'note',
                    ];
                break;
            case 'html':
                if(isset($GLOBALS['note_sn'])){
                    $GLOBALS['note_sn']++;
                }else{
                    $GLOBALS['note_sn'] = 1;
                    $GLOBALS['note'] = array();
                }
                $GLOBALS['note'][] = [
                        'sn' => $GLOBALS['note_sn'],
                        'trigger' => $trigger,
                        'content' => MdRender::render($props["note"],
                                        $this->channel_id,
                                        null,
                                        'read',
                                        'translation',
                                        'markdown',
                                        'html'
                                    ),
                        ];

                $link="<a href='#footnote-".$GLOBALS['note_sn']."' name='note-".$GLOBALS['note_sn']."'>";
                if(empty($trigger)){
                    $output =  $link. "<sup>[" . $GLOBALS['note_sn'] . "]</sup></a>";
                }else{
                    $output = $link . $trigger . "</a>";
                }
                break;
            case 'text':
                $output = $trigger;
                break;
            case 'tex':
                $output = $trigger;
                break;
            case 'simple':
                $output = '';
                break;
            case 'markdown':
                if(isset($GLOBALS['note_sn'])){
                    $GLOBALS['note_sn']++;
                }else{
                    $GLOBALS['note_sn'] = 1;
                    $GLOBALS['note'] = array();
                }
                $content = MdRender::render(
                            $props["note"],
                            $this->channel_id,
                            null,
                            'read',
                            'translation',
                            'markdown',
                            'markdown'
                        );
                $output = '[^'.$GLOBALS['note_sn'].']';
                $GLOBALS['note'][] = [
                    'sn' => $GLOBALS['note_sn'],
                    'trigger' => $trigger,
                    'content' => $content,
                    ];
                //$output = '<footnote id="'.$GLOBALS['note_sn'].'">'.$content.'</footnote>';
                break;
            default:
                $output = '';
                break;
        }
        return $output;
    }
    private  function render_nissaya(){
        $pali =  $this->get_param($this->param,"pali",1);
        $meaning = $this->get_param($this->param,"meaning",2);
        $innerString = "";
        $props = [
            "pali" => $pali,
            "meaning" => $meaning,
            "lang" => $this->lang,
        ];
        switch ($this->format) {
            case 'react':
                $output = [
                    'props'=>base64_encode(\json_encode($props)),
                    'html'=>$innerString,
                    'tag'=>'span',
                    'tpl'=>'nissaya',
                    ];
                break;
            case 'unity':
                $output = [
                    'props'=>base64_encode(\json_encode($props)),
                    'tpl'=>'nissaya',
                    ];
                break;
            case 'text':
                $output = $pali.'၊'.$meaning;
                break;
            case 'tex':
                $output = $pali.'၊'.$meaning;
                break;
            case 'simple':
                $output = $pali.'၊'.$meaning;
                break;
            default:
                $output = $pali.'၊'.$meaning;
                break;
        }
        return $output;
    }
    private  function render_exercise(){

        $id = $this->get_param($this->param,"id",1);
        $title = $this->get_param($this->param,"title",1);
        $props = [
                    "id" => $id,
                    "title" => $title,
                    "channel" => $this->channel_id[0],
                ];
        switch ($this->format) {
            case 'react':
                $output = [
                    'props'=>base64_encode(\json_encode($props)),
                    'html'=>"",
                    'tag'=>'span',
                    'tpl'=>'exercise',
                    ];
                break;
            case 'unity':
                $output = [
                    'props'=>base64_encode(\json_encode($props)),
                    'tpl'=>'exercise',
                    ];
                break;
            case 'text':
                $output = $title;
                break;
            case 'tex':
                $output = $title;
                break;
            case 'simple':
                $output = $title;
                break;
            default:
                $output = '';
                break;
        }
        return $output;
    }
    private  function render_article(){

        $type = $this->get_param($this->param,"type",1);
        $id = $this->get_param($this->param,"id",2);
        $title = $this->get_param($this->param,"title",3);
        $channel = $this->get_param($this->param,"channel",4);
        $style = $this->get_param($this->param,"style",5);
        $book = $this->get_param($this->param,"book",6);
        $paragraphs = $this->get_param($this->param,"paragraphs",7);
        $anthology = $this->get_param($this->param,"anthology",8);
        $props = [
                    "type" => $type,
                    "id" => $id,
                    'style' => $style,
                ];
        if(!empty($channel)){
            $props['channel'] = $channel;
        }
        if(!empty($title)){
            $props['title'] = $title;
        }
        if(!empty($book)){
            $props['book'] = $book;
        }
        if(!empty($paragraphs)){
            $props['paragraphs'] = $paragraphs;
        }
        if(!empty($anthology)){
            $props['anthology'] = $anthology;
        }
        if(is_array($this->channel_id)){
            $props['parentChannels'] = $this->channel_id;
        }
        switch ($this->format) {
            case 'react':
                $output = [
                    'props'=>base64_encode(\json_encode($props)),
                    'html'=>"",
                    'text'=>$title,
                    'tag'=>'span',
                    'tpl'=>'article',
                    ];
                break;
            case 'unity':
                $output = [
                    'props'=>base64_encode(\json_encode($props)),
                    'tpl'=>'article',
                    ];
                break;
            case 'text':
                $output = $title;
                break;
            case 'tex':
                $output = $title;
                break;
            case 'simple':
                $output = $title;
                break;
            default:
                $output = '';
                break;
        }
        return $output;
    }
    private  function render_quote(){
        $paraId = $this->get_param($this->param,"para",1);
        $channelId = $this->channel_id[0];
        $props = RedisClusters::remember("/quote/{$channelId}/{$paraId}",
              config('mint.cache.expire'),
              function() use($paraId,$channelId){
                $para = \explode('-',$paraId);
                $output = [
                    "paraId" => $paraId,
                    "channel" => $channelId,
                    "innerString" => $paraId,
                    ];
                if(count($para)<2){
                    return $output;
                }
                $PaliText = PaliText::where("book",$para[0])
                                    ->where("paragraph",$para[1])
                                    ->select(['toc','path'])
                                    ->first();

                if($PaliText){
                    $output["pali"] = $PaliText->toc;
                    $output["paliPath"] = \json_decode($PaliText->path);
                    $output["innerString"]= $PaliText->toc;
                }
                return $output;
              });

            switch ($this->format) {
                case 'react':
                    $output = [
                        'props'=>base64_encode(\json_encode($props)),
                        'html'=>$props["innerString"],
                        'tag'=>'span',
                        'tpl'=>'quote',
                        ];
                    break;
                case 'unity':
                    $output = [
                        'props'=>base64_encode(\json_encode($props)),
                        'tpl'=>'quote',
                        ];
                    break;
                case 'text':
                    $output = $props["innerString"];
                    break;
                case 'tex':
                    $output = $props["innerString"];
                    break;
                case 'simple':
                    $output = $props["innerString"];
                    break;
                default:
                    $output = $props["innerString"];
                    break;
            }
            return $output;
    }

    private  function render_quote_link(){
        $type = $this->get_param($this->param,"type",1);
        $title = $this->get_param($this->param,"title",6,'');
        $bookName = $this->get_param($this->param,"bookname",2,'');
        $volume = $this->get_param($this->param,"volume",3);
        $page = $this->get_param($this->param,"page",4,'');
        $style = $this->get_param($this->param,"style",5,'modal');
        $book = $this->get_param($this->param,"book",7,false);
        $para = $this->get_param($this->param,"para",8,false);

        $props = [
            'type' => $type,
            'style' => $style,
            'found' => true,
        ];

        if(!empty($bookName) && $volume !== '' && !empty($page)){
            $props['bookName'] = $bookName;
            $props['volume'] = (int)$volume;
            $props['page'] = $page;
            $props['found'] = true;
        }else if($book && $para){
             /**
             * 没有指定书名，根据book para 查询
             */
            if($type==='c'){
                //按照章节名称显示
                $path = PaliTextApi::getChapterPath($book,$para);
                if($path){
                    $path = json_decode($path,true);
                }
                if($path && is_array($path) && count($path)>2){
                    $props['bookName'] = strtolower($path[0]['title']) ;
                    $props['chapter'] = strtolower(end($path)['title']);
                    $props['found'] = true;
                }else{
                    $props['found'] = false;
                }
            }else{
                $pageInfo = $this->pageInfoByPara($type,$book,$para);
                if($pageInfo['found']){
                    $props['bookName'] = $pageInfo['bookName'];
                    $props['volume'] = $pageInfo['volume'];
                    $props['page'] = $pageInfo['page'];
                    $props['found'] = true;
                }else{
                    $props['found'] = false;
                }
            }
        }else if($title){
            //没有书号用title查询
            //$tmpTitle = explode('။',$title);
            for ($i=mb_strlen($title,'UTF-8'); $i > 0 ; $i--) {
                $mTitle = mb_substr($title,0,$i);
                $has = array_search($mTitle, array_column(BookTitle::my(), 'title2'));
                Log::debug('run',['title'=>$mTitle,'has'=>$has]);
                if($has !== false){
                    $tmpBookTitle = $mTitle;
                    $tmpBookPage = mb_substr($title,$i);
                    $tmpBookPage = $this->mb_trim($tmpBookPage,'၊။');
                    break;
                }
            }

            if(isset($tmpBookTitle)){
                Log::debug('book title found',['title'=>$tmpBookTitle,'page'=>$tmpBookPage]);
                //$tmpBookTitle = $tmpTitle[0];
                //$tmpBookPage = $tmpTitle[1];
                $tmpBookPage = (int)str_replace(
                                ['၁','၂','၃','၄','၅','၆','၇','၈','၉','၀'],
                                ['1','2','3','4','5','6','7','8','9','0'],
                                $tmpBookPage);
                $found_key = array_search($tmpBookTitle, array_column(BookTitle::my(), 'title2'));
                if($found_key !== false){
                    $props['bookName'] = BookTitle::my()[$found_key]['bookname'];
                    $props['volume'] = BookTitle::my()[$found_key]['volume'];
                    $props['page'] = $tmpBookPage;
                    if(!empty($props['bookName'])){
                        $found_title = array_search($props['bookName'], array_column(BookTitle::my(), 'bookname'));
                        if($found_title === false){
                            $props['found'] = false;
                        }
                    }
                }else{
                    //没找到，返回术语和页码
                    $props['found'] = false;
                    $props['bookName'] = $tmpBookTitle;
                    $props['page'] = $tmpBookPage;
                    $props['volume'] = 0;
                }
            }
        }else{
            Log::debug('book title not found');
            $props['found'] = false;
        }

        if($book && $para){
            $props['book'] = $book;
            $props['para'] = $para;
        }
        if($title){
            $props['title'] = $title;
        }

        $text = '';
        if(isset($props['bookName'])){
            $searchField = '';
            switch ($type) {
                case 'm':
                    $searchField = 'm_title';
                    break;
                case 'p':
                    $searchField = 'p_title';
                    break;
            }
            $found_title = array_search($props['bookName'], array_column(BookTitle::get(), $searchField));
            if($found_title === false){
                $props['found'] = false;
            }

            $term = $this->getTermProps($props['bookName'],':quote:');
            $props['term'] = $term;
            if(isset($term['id'])){
                $props['bookNameLocal'] = $term['meaning'];
                $text .= $term['meaning'];
            }else{
                $text .= $bookName;
            }
        }

        if(isset($props['volume']) && isset($props['page'])){
            $text .= " {$volume}.{$page}";
        }


        switch ($this->format) {
            case 'react':
                $output = [
                    'props'=>base64_encode(\json_encode($props)),
                    'html'=>'',
                    'tag'=>'span',
                    'tpl'=>'quote-link',
                    ];
                break;
            case 'unity':
                $output = [
                    'props'=>base64_encode(\json_encode($props)),
                    'tpl'=>'quote-link',
                    ];
                break;
            default:
                $output = $text;
                break;
        }
        return $output;
    }

    private function pageInfoByPara($type,$book,$para){
        $output = array();
        $pageInfo = PageNumber::where('type',strtoupper($type))
                ->where('book',$book)
                ->where('paragraph','<=',$para)
                ->orderBy('paragraph','desc')
                ->first();
        if($pageInfo){
            foreach (BookTitle::get() as $value) {
                if($value['id']===$pageInfo->pcd_book_id){
                    switch (strtoupper($type)) {
                        case 'M':
                            $key = 'm_title';
                            break;
                        case 'P':
                            $key = 'p_title';
                            break;
                        case 'V':
                            $key = 'v_title';
                            break;
                        default:
                            $key = 'term';
                            break;
                    }
                    $output['bookName'] = $value[$key];
                    break;
                }
            }
            $output['volume'] = $pageInfo->volume;
            $output['page'] = $pageInfo->page;
            $output['found'] = true;
        }else{
            $output['found'] = false;
        }
        return $output;
    }
    private  function render_sent(){

        $sid = $this->get_param($this->param,"id",1);
        $channel = $this->get_param($this->param,"channel",2);
        $text = $this->get_param($this->param,"text",2,'both');

        if(!empty($channel)){
            $channels = explode(',',$channel);
        }else{
            $channels = $this->channel_id;
        }
        $sentInfo = explode('@',trim($sid));
        $sentId = $sentInfo[0];
        if(isset($sentInfo[1])){
            $channels = [$sentInfo[1]];
        }
        $Sent = new CorpusController();
        $props = $Sent->getSentTpl($sentId,$channels,
                                   $this->mode,true,
                                   $this->format);
        if($props === false){
            $props['error']="句子模版渲染错误。句子参数个数不符。应该是四个。";
        }
        if($this->mode==='read'){
            $tpl = "sentread";
        }else{
            $tpl = "sentedit";
        }

        //输出引用
        $arrSid = explode('-',$sid);
        $bookPara = array_slice($arrSid,0,2);
        if(!isset($GLOBALS['ref_sent'])){
            $GLOBALS['ref_sent'] = array();
        }
        $GLOBALS['ref_sent'][] = $bookPara;

        switch ($this->format) {
            case 'react':
                $output = [
                    'props'=>base64_encode(\json_encode($props)),
                    'html'=>"",
                    'tag'=>'span',
                    'tpl'=>$tpl,
                    ];
                break;
            case 'unity':
                $output = [
                    'props'=>base64_encode(\json_encode($props)),
                    'tpl'=>$tpl,
                    ];
                break;
            case 'text':
                $output = '';
                if(isset($props['origin']) && is_array($props['origin'])){
                    foreach ($props['origin'] as $key => $value) {
                        $output .= $value['html'];
                    }
                }
                if(isset($props['translation']) && is_array($props['translation'])){
                    foreach ($props['translation'] as $key => $value) {
                        $output .= $value['html'];
                    }
                }
                break;
            case 'html':
                $output = '';
                $output .= '<span class="sentence">';
                if($text === 'both' || $text === 'origin'){
                    if(isset($props['origin']) && is_array($props['origin'])){
                        foreach ($props['origin'] as $key => $value) {
                            $output .= '<span class="origin">'.$value['html'].'</span>';
                        }
                    }
                }
                if($text === 'both' || $text === 'translation'){
                    if(isset($props['translation']) && is_array($props['translation'])){
                        foreach ($props['translation'] as $key => $value) {
                            $output .= '<span class="translation">'.$value['html'].'</span>';
                        }
                    }
                }

                $output .= '</span>';
                break;
            case 'tex':
                $output = '';
                if(isset($props['translation']) && is_array($props['translation'])){
                    foreach ($props['translation'] as $key => $value) {
                        $output .= $value['html'];
                    }
                }
                break;
            case 'simple':
                $output = '';
                if($text === 'both' || $text === 'origin'){
                    if(empty($output)){
                        if(isset($props['origin']) &&
                                is_array($props['origin']) &&
                                count($props['origin']) > 0
                                ){
                            foreach ($props['origin'] as $key => $value) {
                                $output .= trim($value['html']);
                            }
                        }
                    }
                }
                if($text === 'both' || $text === 'translation'){
                    if(isset($props['translation']) &&
                    is_array($props['translation']) &&
                    count($props['translation']) > 0
                    ){
                        foreach ($props['translation'] as $key => $value) {
                            $output .= trim($value['html']);
                        }
                    }
                }
                break;
            case 'markdown':
                $output = '';
                if($text === 'both' || $text === 'origin'){
                    if($this->options['origin'] === true ||
                       $this->options['origin'] === 'true'){
                        if(isset($props['origin']) && is_array($props['origin'])){
                            foreach ($props['origin'] as $key => $value) {
                                $output .= trim($value['html']);
                            }
                        }
                    }
                }
                if($text === 'both' || $text === 'translation'){
                    if($this->options['translation']  === true ||
                       $this->options['translation']  === 'true'){
                        if(isset($props['translation']) &&
                            is_array($props['translation']) &&
                            count($props['translation'])>0){
                            foreach ($props['translation'] as $key => $value) {
                                $output .= trim($value['html']);
                            }
                        }else{
                            if($text === 'translation'){
                                //无译文用原文代替
                                if(isset($props['origin']) && is_array($props['origin'])){
                                    foreach ($props['origin'] as $key => $value) {
                                        $output .= trim($value['html']);
                                    }
                                }
                            }
                        }
                    }
                }
                break;
            default:
                $output = '';
                break;
        }
        return $output;
    }

    private  function render_mermaid(){
        $text = json_decode(base64_decode($this->get_param($this->param,"text",1)));

        $props = ["text" => implode("\n",$text)];

        switch ($this->format) {
            case 'react':
                $output = [
                    'props'=>base64_encode(\json_encode($props)),
                    'html'=>"mermaid",
                    'tag'=>'div',
                    'tpl'=>'mermaid',
                    ];
                break;
            case 'unity':
                $output = [
                    'props'=>base64_encode(\json_encode($props)),
                    'tpl'=>'mermaid',
                    ];
                break;
            case 'text':
                $output = 'mermaid';
                break;
            case 'tex':
                $output = 'mermaid';
                break;
            case 'simple':
                $output = 'mermaid';
                break;
            default:
                $output = 'mermaid';
                break;
        }
        return $output;
    }

    private  function render_qa(){

        $id = $this->get_param($this->param,"id",1);
        $style = $this->get_param($this->param,"style",2);

        $props = [
                    "type" => 'qa',
                    "id" => $id,
                    'title' => '',
                    'style' => $style,
                ];
        $qa = Discussion::where('id',$id)->first();
        if($qa){
            $props['title'] = $qa->title;
            $props['resId'] = $qa->res_id;
            $props['resType'] = $qa->res_type;
        }

        switch ($this->format) {
            case 'react':
                $output = [
                    'props'=>base64_encode(\json_encode($props)),
                    'html'=>"",
                    'text'=>$props['title'],
                    'tag'=>'div',
                    'tpl'=>'qa',
                    ];
                break;
            case 'unity':
                $output = [
                    'props'=>base64_encode(\json_encode($props)),
                    'tpl'=>'qa',
                    ];
                break;
            default:
                $output = $props['title'];
                break;
        }
        return $output;
    }

    private function render_grammar_lookup(){
        $word = $this->get_param($this->param,"word",1);
        $props = ['word' => $word];

        $localTermChannel = ChannelApi::getSysChannel(
            "_System_Grammar_Term_".strtolower($this->lang)."_",
            "_System_Grammar_Term_en_"
        );
        $term = $this->getTermProps($word,null,$localTermChannel);
        $props['term'] = $term;
        switch ($this->format) {
            case 'react':
                $output = [
                    'props'=>base64_encode(\json_encode($props)),
                    'html'=>"",
                    'text'=>$props['word'],
                    'tag'=>'span',
                    'tpl'=>'grammar',
                    ];
                break;
            case 'unity':
                $output = [
                    'props'=>base64_encode(\json_encode($props)),
                    'tpl'=>'grammar',
                    ];
                break;
            default:
                $output = $props['word'];
                break;
        }
        return $output;
    }

    private  function render_video(){

        $url = $this->get_param($this->param,"url",1);
        $style = $this->get_param($this->param,"style",2,'modal');
        $title = $this->get_param($this->param,"title",3);

        $props = [
                    "url" => $url,
                    'title' => $title,
                    'style' => $style,
                ];

        switch ($this->format) {
            case 'react':
                $output = [
                    'props'=>base64_encode(\json_encode($props)),
                    'html'=>"",
                    'text'=>$props['title'],
                    'tag'=>'span',
                    'tpl'=>'video',
                    ];
                break;
            case 'unity':
                $output = [
                    'props'=>base64_encode(\json_encode($props)),
                    'tpl'=>'video',
                    ];
                break;
            default:
                $output = $props['title'];
                break;
        }
        return $output;
    }

    //论文后面的参考资料
    private  function render_ref(){
        $references = array();
        $counter = 0;

        if(isset($GLOBALS['ref_sent'])){
            $hasBooks = array();
            $book_titles = BookSeries::select(['book','paragraph','title','sn'])
                                ->orderBy('sn','DESC')->get();
            $bTitles = array();
            foreach ($book_titles as $key => $book) {
                $bTitles[] = [
                    'book'=>$book->book,
                    'paragraph'=>$book->paragraph,
                    'title'=>$book->title
                ];
            }
            foreach ($GLOBALS['ref_sent'] as $key => $ref) {
                $books = array_filter($bTitles,function($value) use($ref){
                    return $value['book'] === (int)$ref[0];
                });
                if(count($books)>0){
                    foreach ($books as $key => $book) {
                        if($book['paragraph'] < (int)$ref[1]){
                            if(!isset($hasBooks[$book['title']])){
                                $hasBooks[$book['title']] = 1;
                                $counter++;
                                $references[] = [
                                    'sn'=>$counter,
                                    'title'=>$book['title'],
                                    'copyright'=>'CSCD V4 VRI 2008'
                                ];
                            }
                        }
                    }
                }
            }
        }
        $props = [
                    "pali" => $references,
                ];

        switch ($this->format) {
            case 'react':
                $output = [
                    'props'=>base64_encode(\json_encode($props)),
                    'html'=>'',
                    'tag'=>'div',
                    'tpl'=>'reference',
                    ];
                break;
            case 'unity':
                $output = [
                    'props'=>base64_encode(\json_encode($props)),
                    'tpl'=>'reference',
                    ];
                break;
            case 'markdown':
                $output = '';
                foreach ($references as $key => $reference) {
                    $output .= '[' . $reference['sn'] . '] **' . ucfirst($reference['title']) . '** ';
                    $output .= $reference['copyright']. "\n\n";
                }
                break;
            default:
                $output = '';
                foreach ($references as $key => $reference) {
                    $output .= '[' . $reference['sn'] . '] ' . ucfirst($reference['title']) . ' ';
                    $output .= $reference['copyright']. "\n";
                }
                break;
        }
        return $output;
    }

    private  function get_param(array $param,string $name,int $id,string $default=''){
        if(isset($param[$name])){
            return trim($param[$name]);
        }else if(isset($param["{$id}"])){
            return trim($param["{$id}"]);
        }else{
            return $default;
        }
    }

    private function mb_trim($str,string $character_mask = ' ', $charset = "UTF-8") {
        $start = 0;
        $end = mb_strlen($str, $charset) - 1;
        $chars = preg_split('//u', $character_mask, -1, PREG_SPLIT_NO_EMPTY);
        while ($start <= $end && in_array(mb_substr($str, $start, 1, $charset),$chars)) {
            $start++;
        }

        while ($end >= $start && in_array(mb_substr($str, $end, 1, $charset),$chars) ) {
            $end--;
        }

        return mb_substr($str, $start, $end - $start + 1, $charset);
    }
}
