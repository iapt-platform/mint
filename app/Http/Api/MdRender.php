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
    public static function wiki2xml(string $wiki):string{
        /**
         * 替换{{}} 到xml之前 要先把换行符号去掉
         */
        $html = str_replace("\n","",$wiki);

        $pattern = "/\{\{(.+?)\|/";
        $replacement = '<MdTpl name="$1"><param>';
        $html = preg_replace($pattern,$replacement,$html);
        $html = str_replace("}}","</param></MdTpl>",$html);
        $html = str_replace("|","</param><param>",$html);

        /**
         * 替换变量名
         */

        $pattern = "/<param>([a-z]+?)=/";
        $replacement = '<param name="$1">';
        $html = preg_replace($pattern,$replacement,$html);

        $html = str_replace("<p>","<div>",$html);
        $html = str_replace("</p>","</div>",$html);
        $html = "<xml>".$html."</xml>";
        return $html;
    }
    public static function xmlQueryId(string $xml, string $id):string{
        $dom = simplexml_load_string($xml);
        $tpl_list = $dom->xpath('//MdTpl');
        foreach ($tpl_list as $key => $tpl) {
            foreach ($tpl->children() as  $param) {
                # 处理每个参数
                if($param->getName() === "param"){
                    foreach($param->attributes() as $pa => $pa_value){
                        $pValue = $pa_value->__toString();
                        if($pa === "name" && $pValue === "id"){
                            if($param->__toString() === $id){
                                return $tpl->asXML();
                            }
                        }
                    }
                }
            }
        }
        return "<div></div>";
    }
    public static function take_sentence(string $xml):array{
        $output = [];
        $dom = simplexml_load_string($xml);
        $tpl_list = $dom->xpath('//MdTpl');
        foreach ($tpl_list as $key => $tpl) {
            foreach($tpl->attributes() as $a => $a_value){
                if($a==="name"){
                    if($a_value->__toString() ==="sent"){
                        foreach ($tpl->children() as  $param) {
                            # 处理每个参数
                            if($param->getName() === "param"){
                                $sent = $param->__toString();
                                if(!empty($sent)){
                                    $output[] = $sent;
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }
        return $output;
    }
    public static function xml2tpl(string $xml, $channelId="",$mode='read'):string{
        /**
         * 解析xml
         * 获取模版参数
         * 生成react 组件参数
         */
        //$xml = str_replace(['<b>','</b>'],['',''],$xml);
        $dom = simplexml_load_string($xml);

        $tpl_list = $dom->xpath('//MdTpl');
        foreach ($tpl_list as $key => $tpl) {
            /**
             * 遍历 MdTpl 处理参数
             */
            $props = [];
            $tpl_name = '';
            foreach($tpl->attributes() as $a => $a_value){
                if($a==="name"){
                    $tpl_name = $a_value;
                }
            }
            $param_id = 0;
            foreach ($tpl->children() as  $param) {
                # 处理每个参数
                if($param->getName() === "param"){
                    $param_id++;
                    $props["{$param_id}"] = $param->__toString();
                    foreach($param->attributes() as $pa => $pa_value){
                        if($pa === "name"){
                            $props["{$pa_value}"] = $param->__toString();
                        }
                    }
                }
            }
            /**
             * 生成模版参数
             */
            $tplRender = new TemplateRender($props,$channelId,$mode);
            $tplProps = $tplRender->render($tpl_name);
            if($tplProps){
                $tpl->addAttribute("props",$tplProps['props']);
                $tpl->addAttribute("tpl",$tplProps['tpl']);
                $tpl->addChild($tplProps['tag'],$tplProps['html']);
            }
        }
        $html = str_replace('<?xml version="1.0"?>','',$dom->asXML()) ;
        $html = str_replace(['<xml>','</xml>'],['<span>','</span>'],$html);
        return $html;
    }

    public static function render2($markdown,$channelId='',$queryId=null,$mode='read'){
        $wiki = MdRender::markdown2wiki($markdown);
        $html = MdRender::wiki2xml($wiki);
        if(!is_null($queryId)){
            $html = MdRender::xmlQueryId($html, $queryId);
        }
        $tpl = MdRender::xml2tpl($html,$channelId,$mode);
        return $tpl;
    }
    public static function markdown2wiki(string $markdown): string{
        /**
         * 替换换行符
         * react 无法处理 <br> 替换为<div></div>代替换行符作用
         */
        $markdown = str_replace('<br>','<div></div>',$markdown);

        /**
         * markdown -> html
         */
        $html = Str::markdown($markdown);

        #替换术语
        $pattern = "/\[\[(.+?)\]\]/";
        $replacement = '{{term|$1}}';
        $html = preg_replace($pattern,$replacement,$html);

        #替换句子模版
        $pattern = "/\{\{([0-9].+?)\}\}/";
        $replacement = '{{sent|$1}}';
        $html = preg_replace($pattern,$replacement,$html);

        #替换注释
        #<code>bla</code>
        #{{note:bla}}
        $pattern = '/<code>(.+?)<\/code>/';
        $replacement = '{{note|$1}}';
        $html = preg_replace($pattern,$replacement,$html);
        return $html;
    }

    /**
     *
     */
    public static function render($markdown,$channelId,$queryId=null,$mode='read'){
        return MdRender::render2($markdown,$channelId,$queryId,$mode);

        $html = MdRender::markdown2wiki($markdown);

        /**
         * 转换为Mustache模版
         */
        $pattern = "/\{\{(.+?)\}\}/";
        $replacement = "\n{{#function}}\n$1\n{{/function}}\n";
        $html = preg_replace($pattern,$replacement,$html);

        /**
         * Mustache_Engine 处理Mustache模版
         * 把Mustache模版内容转换为react组件
         */
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
                    $sentInfo = explode('@',trim($param[1]));
                    $sentId = $sentInfo[0];
                    $Sent = new CorpusController();
                    if(empty($channelId)){
                        $channels = [];
                    }else{
                        $channels = [$channelId];
                    }
                    if(isset($sentInfo[1])){
                        $channels = [$sentInfo[1]];
                    }
                    $html = $Sent->getSentTpl($param[1],$channels);
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
                case 'exercise':
                    $exeId = trim($param[1]);
                    $exeContent = trim($param[2]);
                    $props = Cache::remember("/quote/{$channelId}/{$exeId}",
                          60,
                          function() use($exeId,$channelId){
                            $output = [
                                "id" => $exeId,
                                "channel" => $channelId,
                                ];
                            return $output;
                          });
                    #替换句子
                    $pattern = "/\(\((.+?)\)\)/";
                    $replacement = '{{sent|$1}}';
                    Log::info("content{$exeContent}");

                    $exeContent = preg_replace($pattern,$replacement,$exeContent);
                    Log::info("content{$exeContent}");
                    $innerString = MdRender::render($exeContent,$channelId);
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
        return "<xml>{$html}</xml>";
    }

}
