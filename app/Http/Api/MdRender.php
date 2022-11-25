<?php
namespace App\Http\Api;

use Illuminate\Support\Str;
use mustache\mustache;
use App\Models\DhammaTerm;
use App\Models\PaliText;
use App\Http\Controllers\CorpusController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MdRender{
    /**
     *
     */
    public static function render($markdown,$channelId,$isArticle=false){

        $html = Str::markdown($markdown);
        #替换术语
        $pattern = "/\[\[(.+?)\]\]/";
        $replacement = '{{term|$1}}';
        $html = preg_replace($pattern,$replacement,$html);

        #替换句子
        $pattern = "/\{\{([0-9].+?)\}\}/";
        $replacement = '{{sent|$1}}';
        $html = preg_replace($pattern,$replacement,$html);

        #替换注释
        #<code>bla</code>
        #{{note:bla}}
        #替换术语
        $pattern = '/<code>(.+?)<\/code>/';
        $replacement = '{{note|$1}}';
        $html = preg_replace($pattern,$replacement,$html);

        $pattern = "/\{\{(.+?)\}\}/";
        $replacement = "\n{{#function}}\n$1\n{{/function}}\n";
        $html = preg_replace($pattern,$replacement,$html);
        $m = new \Mustache_Engine(array('entity_flags' => ENT_QUOTES));
        $html = $m->render($html, array(
          'function' => function($text) use($m,$channelId) {
            //1: 解析
            $param = explode("|",$text);
            //3: 处理业务逻辑
            $tplName = trim($param[0]);
            $innerString = "";
            switch($tplName){
                case 'term':
                    //获取实际的参数
                    $word = trim($param[1]);
                    $props = Cache::remember("/term/{$channelId}/{$word}",
                          60,
                          function() use($word,$channelId){
                            $tplParam = DhammaTerm::where("word",$word)->first();
                            $output = [
                                "word" => $word,
                                "channel" => $channelId,
                                ];
                                $innerString = $output["word"];
                            if($tplParam){
                                $output["id"] = $tplParam->guid;
                                $output["meaning"] = $tplParam->meaning;
                                $innerString = $output["meaning"];
                                if(!empty($tplParam->other_meaning)){
                                    $output["meaning2"] = $tplParam->other_meaning;
                                }
                            }
                            return $output;
                          });
                    break;
                case 'note':
                    if(isset($param[1])){
                        $props = ["note"=>trim($param[1])];
                    }
                    if(isset($param[2])){
                        $props["trigger"] = trim($param[2]);
                        $innerString = $props["trigger"];
                    }
                    break;
                case 'sent':
                    $tplName = "sentedit";
                    $innerString = "";
                    $sentId = trim($param[1]);
                    $Sent = new CorpusController();
                    $html = $Sent->getSentTpl($param[1],[$channelId]);
                    return $html;
                    break;
                case 'quote':
                    $paraId = trim($param[1]);
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
                                $innerString = $PaliText->toc;
                            }
                            return $output;
                          });
                    break;
                default:
                    break;
            }
            //4: 返回拼好的字符串

            $props = base64_encode(\json_encode($props));
            $html = "<MdTpl tpl='{$tplName}' props='{$props}' >{$innerString}</MdTpl>";
            return $html;
          }
        ));
        if(substr_count($html,"<p>") === 1){
            $html = \str_replace(['<p>','</p>'],'',$html);
        }
        //LOG::info($html);
        return $html;
    }
}
