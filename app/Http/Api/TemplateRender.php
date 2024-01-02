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
use App\Http\Controllers\CorpusController;

use App\Tools\RedisClusters;
use App\Http\Api\ChannelApi;
use App\Http\Api\MdRender;


class TemplateRender{
    protected $param = [];
    protected $mode = "read";
    protected $channel_id = [];
    protected $debug = [];
    protected $format = 'react';
    protected $studioId = null;


    /**
     * Create a new command instance.
     * string $mode  'read' | 'edit'
     * string $format  'react' | 'text' | 'tex' | 'unity'
     * @return void
     */
    public function __construct($param, $channelInfo, $mode,$format='react',$studioId,$debug=[])
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

    public function getTermProps($word,$tag){
        if(count($this->channel_id)>0){
            $channelId = $this->channel_id[0];
        }else{
            $channelId = null;
        }


        if(count($this->channelInfo)===0){
            Log::error('channel is null');
            $output = [
                "word" => $word,
                'innerHtml' => '',
            ];
            return $output;
        }
        $channelInfo = $this->channelInfo[0];
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
            if(!empty($tag)){
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

        //$key = "/term/{$channelId}/{$word}";
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
            $props["note"] = MdRender::render($props["note"],
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
                        'sn' => 1,
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
        $channel = $this->get_param($this->param,"channel",4,$this->channel_id[0]);
        $style = $this->get_param($this->param,"style",5);
        $props = [
                    "type" => $type,
                    "id" => $id,
                    "channel" => $channel,
                    'style' => $style,
                ];
        if(!empty($title)){
            $props['title'] = $title;
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

        if(empty($bookName) || $volume==='' || empty($page)){
            /**
             * 没有指定书名，根据book para 查询
             */
            if($book && $para){
                $pageInfo = PageNumber::where('type',strtoupper($type))
                                ->where('book',$book)
                                ->where('paragraph','<=',$para)
                                ->orderBy('paragraph','desc')
                                ->first();
                if($pageInfo){
                    if(!$bookName){
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
                                $bookName = $value[$key];
                            }
                        }
                    }
                    if( $volume === '' ){
                        $volume = $pageInfo->volume;
                    }
                    if(!$page){
                        $page = $pageInfo->page;
                    }
                }else{
                    $props['found'] = false;
                }
            }else{
                //没有书号用title查询
                if($title){
                    $tmpTitle = explode('။',$title);
                    if(count($tmpTitle)>1){
                        $tmpBookTitle = $tmpTitle[0];
                        $tmpBookPage = $tmpTitle[1];
                        $tmpBookPage = (int)str_replace(
                                        ['၁','၂','၃','၄','၅','၆','၇','၈','၉','၀'],
                                        ['1','2','3','4','5','6','7','8','9','0'],
                                        $tmpBookPage);
                        $found_key = array_search($tmpBookTitle, array_column(BookTitle::my(), 'title2'));
                        if($found_key !== false){
                            $bookName = BookTitle::my()[$found_key]['bookname'];
                            $volume = BookTitle::my()[$found_key]['volume'];
                            $page = $tmpBookPage;
                        }else{
                            //没找到，返回术语和页码
                            $props['found'] = false;
                            $bookName = $tmpBookTitle;
                            $page = $tmpBookPage;
                            $volume = 0;
                        }
                    }
                }else{
                    $props['found'] = false;
                }
            }
        }

        if(!empty($bookName)){
            $found_title = array_search($bookName, array_column(BookTitle::my(), 'bookname'));
            if($found_title === false){
                $props['found'] = false;
            }
        }
        if(!empty($bookName) && $volume !== '' && !empty($page)){
            $props['bookName'] = $bookName;
            $props['volume'] = (int)$volume;
            $props['page'] = $page;
            $props['found'] = true;
        }
        if($book && $para){
            $props['book'] = $book;
            $props['para'] = $para;
        }
        if($title){
            $props['title'] = $title;
        }
        $term = $this->getTermProps($bookName,':quote:');
        $props['term'] = $term;
        if(isset($term['id'])){
            $props['bookNameLocal'] = $term['meaning'];
            $text = $term['meaning'];
        }else{
            $text = $bookName;
        }
        $text .= " {$volume}.{$page}";

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

    private  function render_sent(){

        $sid = $this->get_param($this->param,"sid",1);
        $channel = $this->get_param($this->param,"channel",2);
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
                if(isset($props['translation']) && is_array($props['translation'])){
                    foreach ($props['translation'] as $key => $value) {
                        $output .= $value['html'];
                    }
                }
                break;
            case 'html':
                $output = '';
                if(isset($props['translation']) && is_array($props['translation'])){
                    foreach ($props['translation'] as $key => $value) {
                        $output .= '<span class="sentence">'.$value['html'].'</span>';
                    }
                }
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
                if(isset($props['translation']) &&
                   is_array($props['translation']) &&
                   count($props['translation']) > 0
                   ){
                    $sentences = $props['translation'];
                    foreach ($sentences as $key => $value) {
                        $output .= $value['html'];
                    }
                }
                if(empty($output)){
                    if(isset($props['origin']) &&
                            is_array($props['origin']) &&
                            count($props['origin']) > 0
                            ){
                        $sentences = $props['origin'];
                        foreach ($sentences as $key => $value) {
                            $output .= $value['html'];
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

    private  function get_param(array $param,string $name,int $id,string $default=''){
        if(isset($param[$name])){
            return trim($param[$name]);
        }else if(isset($param["{$id}"])){
            return trim($param["{$id}"]);
        }else{
            return $default;
        }
    }
}
