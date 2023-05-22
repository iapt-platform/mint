<?php
namespace App\Http\Api;

use App\Models\DhammaTerm;
use App\Models\PaliText;
use App\Http\Controllers\CorpusController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TemplateRender{
    protected $param = [];
    protected $mode = "read";
    protected $channel_id = "";

    /**
     * Create a new command instance.
     * int $mode  'read' | 'edit'
     * @return void
     */
    public function __construct($param, $channelInfo, $mode)
    {
        $this->param = $param;
        $this->channel_id = $channelInfo->uid;
        $this->channelInfo = $channelInfo;
        $this->mode = $mode;
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

    private function render_term(){
        $word = $this->get_param($this->param,"word",1);
        $channelId = $this->channel_id;
        $channelInfo = $this->channelInfo;
        $props = Cache::remember("/term/{$this->channel_id}/{$word}",
              60,
              function() use($word,$channelId,$channelInfo){
                //先查属于这个channel 的
                $tplParam = DhammaTerm::where("word",$word)->where('channal',$channelId)->first();
                if(!$tplParam){
                    //没有，再查这个studio的
                    $tplParam = DhammaTerm::where("word",$word)
                                          ->where('owner',$channelInfo->owner_uid)
                                          ->first();
                }
                $output = [
                    "word" => $word,
                    "channel" => $channelId,
                    ];
                    $innerString = $output["word"];
                if($tplParam){
                    $output["id"] = $tplParam->guid;
                    $output["meaning"] = $tplParam->meaning;
                    $innerString = "{$output["meaning"]}({$output["word"]})";
                    if(!empty($tplParam->other_meaning)){
                        $output["meaning2"] = $tplParam->other_meaning;
                    }
                    if($tplParam->note){
                        $output["summary"] = $tplParam->note;
                    }else{
                        //使用社区note
                        //获取channel 语言
                        //查找社区解释
                    }
                }
                $output['innerHtml'] = $innerString;
                return $output;
              });
        return [
            'props'=>base64_encode(\json_encode($props)),
            'html'=>$props['innerHtml'],
            'tag'=>'span',
            'tpl'=>'term',
            ];
    }

    private  function render_note(){

        $props = ["note" => $this->get_param($this->param,"text",1)];
        $trigger = $this->get_param($this->param,"trigger",2);
        $innerString = "";
        if(!empty($trigger)){
            $props["trigger"] = $trigger;
            $innerString = $props["trigger"];
        }
        return [
            'props'=>base64_encode(\json_encode($props)),
            'html'=>$innerString,
            'tag'=>'span',
            'tpl'=>'note',
            ];
    }
    private  function render_nissaya(){

        $pali =  $this->get_param($this->param,"pali",1);
        $meaning = $this->get_param($this->param,"meaning",2);
        $innerString = "";
        $props = [
            "pali" => $pali,
            "meaning" => $meaning,
        ];
        return [
            'props'=>base64_encode(\json_encode($props)),
            'html'=>$innerString,
            'tag'=>'span',
            'tpl'=>'nissaya',
            ];
    }
    private  function render_exercise(){

        $id = $this->get_param($this->param,"id",1);
        $title = $this->get_param($this->param,"title",1);
        $props = [
                    "id" => $id,
                    "title" => $title,
                    "channel" => $this->channel_id,
                ];

        return [
            'props'=>base64_encode(\json_encode($props)),
            'html'=>"",
            'tag'=>'span',
            'tpl'=>'exercise',
            ];
    }
    private  function render_article(){

        $type = $this->get_param($this->param,"type",1);
        $id = $this->get_param($this->param,"id",2);
        $title = $this->get_param($this->param,"title",3);
        $channel = $this->get_param($this->param,"channel",4);
        $props = [
                    "type" => $type,
                    "id" => $id,
                    "channel" => $channel,
                ];
        if(empty($channel)){
            $props['channel'] = $this->channel_id;
        }
        if(!empty($title)){
            $props['title'] = $title;
        }
        return [
            'props'=>base64_encode(\json_encode($props)),
            'html'=>"",
            'tag'=>'span',
            'tpl'=>'article',
            ];
    }
    private  function render_quote(){
        $paraId = $this->get_param($this->param,"para",1);
        $channelId = $this->channel_id;
        $props = Cache::remember("/quote/{$channelId}/{$paraId}",
              60,
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
        return [
            'props'=>base64_encode(\json_encode($props)),
            'html'=>$props["innerString"],
            'tag'=>'span',
            'tpl'=>'quote',
            ];
    }
    private  function render_sent(){

        $sid = $this->get_param($this->param,"sid",1);
        $channel = $this->get_param($this->param,"channel",2);
        if(!empty($channel)){
            $mChannel = $channel;
        }else{
            $mChannel = $this->channel_id;
        }
        $sentInfo = explode('@',trim($sid));
        $sentId = $sentInfo[0];
        if(empty($mChannel)){
            $channels = [];
        }else{
            $channels = [$mChannel];
        }
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
        return [
            'props'=>base64_encode(\json_encode($props)),
            'html'=>"",
            'tag'=>'span',
            'tpl'=>$tpl,
            ];
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
