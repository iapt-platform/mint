<?php
namespace App\Http\Api;

use App\Models\DhammaTerm;
use App\Models\PaliText;
use App\Models\Channel;
use App\Http\Controllers\CorpusController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Http\Api\ChannelApi;
use App\Http\Api\MdRender;

class TemplateRender{
    protected $param = [];
    protected $mode = "read";
    protected $channel_id = [];
    protected $format = 'react';


    /**
     * Create a new command instance.
     * string $mode  'read' | 'edit'
     * string $format  'react' | 'text' | 'tex' | 'unity'
     * @return void
     */
    public function __construct($param, $channelInfo, $mode,$format='react')
    {
        $this->param = $param;
        foreach ($channelInfo as $value) {
            $this->channel_id[] = $value->uid;
        }
        $this->channelInfo = $channelInfo;
        $this->mode = $mode;
        $this->format = $format;
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

    public function getTermProps($word,$channelId,$channelInfo){
        $lang = Channel::where('uid',$channelId)->value('lang');
        if(!empty($lang)){
            $langFamily = explode('-',$lang)[0];
        }else{
            $langFamily = 'zh';
        }
        //先查属于这个channel 的
        $tplParam = DhammaTerm::where("word",$word)
                              ->where('channal',$channelId)
                              ->orderBy('updated_at','desc')
                              ->first();
        if(!$tplParam){
            /**
             * 没有，再查这个studio的
             * 按照语言过滤
             * 完全匹配的优先
             * 语族匹配也行
             */
            $termsInStudio = DhammaTerm::where("word",$word)
                                  ->where('owner',$channelInfo->owner_uid)
                                  ->orderBy('updated_at','desc')
                                  ->get();
            if(count($termsInStudio)>0){
                $list = array();
                foreach ($termsInStudio as $key=>$term) {
                    if(empty($term->channal)){
                        if($term->language===$lang){
                            $list[$term->guid]=2;
                        }else if(strpos($term->language,$langFamily)!==false){
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
        if(!$tplParam){
            //没有，再查社区

            $community_channel = ChannelApi::getSysChannel("_community_term_zh-hans_");
            $tplParam = DhammaTerm::where("word",$word)
                                  ->where('channal',$community_channel)
                                  ->first();
            if($tplParam){
                $isCommunity = true;
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
            /*
            if($tplParam->note){
                $output["summary"] = $tplParam->note;
            }else{
                //本人没有解释内容的。用社区数据。
                //TODO 由作者（读者）设置是否使用社区数据
                //获取channel 语言
                //使用社区note
                $community_channel = ChannelApi::getSysChannel("_community_term_zh-hans_");
                //查找社区解释
                $community_note = DhammaTerm::where("word",$word)
                                            ->where('channal',$community_channel)
                                            ->value('note');
                $output["summary"] = $tplParam->note;
                $output["community"] = true;
            }
            */
        }
        $output['innerHtml'] = $innerString;
        return $output;
    }

    private function render_term(){
        $word = $this->get_param($this->param,"word",1);
        $channelId = $this->channel_id[0];
        $channelInfo = $this->channelInfo[0];
        $key = "/term/{$channelId}/{$word}";
        $props = $this->getTermProps($word,$channelId,$channelInfo);

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
            case 'text':
                $output = $trigger;
                break;
            case 'tex':
                $output = $trigger;
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
            default:
                $output = '';
                break;
        }
        return $output;
    }
    private  function render_quote(){
        $paraId = $this->get_param($this->param,"para",1);
        $channelId = $this->channel_id[0];
        $props = Cache::remember("/quote/{$channelId}/{$paraId}",
              env('CACHE_EXPIRE',3600*24),
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
                default:
                    $output = '';
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
        $props = $Sent->getSentTpl($sentId,$channels,$this->mode,true);
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
                break;
            case 'tex':
                $output = '';
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
