<?php
namespace App\Http\Api;

use Illuminate\Support\Str;
use mustache\mustache;
use App\Models\DhammaTerm;
use App\Models\PaliText;
use App\Models\Channel;
use App\Http\Controllers\CorpusController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
define("STACK_DEEP",8);

class MdRender{

    public static function tplSplit($tpl){
        $before = strpos($tpl,'{{');
        if($before === FALSE){
            //未找到
            return ['data'=>[$tpl,'',''],'error'=>0];
        }else{
            $pointer = $before;
            $stack = array();
            $stack[] = $pointer;
            $after = substr($tpl,$pointer+2) ;
            while (!empty($after) && count($stack)>0 && count($stack)<STACK_DEEP) {
                $nextBegin = strpos($after,"{{");
                $nextEnd = strpos($after,"}}");
                if($nextBegin !== FALSE){
                    if($nextBegin < $nextEnd){
                        //有嵌套找到最后一个}}
                        $pointer = $pointer + 2 + $nextBegin;
                        $stack[] = $pointer;
                        $after = substr($tpl,$pointer+2);
                    }else if($nextEnd !== FALSE){
                        //无嵌套有结束
                        $pointer = $pointer + 2 + $nextEnd;
                        array_pop($stack);
                        $after = substr($tpl,$pointer+2);
                    }else{
                        //无结束符 没找到
                        break;
                    }
                }else if($nextEnd !== FALSE){
                    $pointer = $pointer + 2 + $nextEnd;
                    array_pop($stack);
                    $after = substr($tpl,$pointer+2);
                }else{
                    //没找到
                    break;
                }
            }
            if(count($stack)>0){
                if(count($stack) === STACK_DEEP){
                    return ['data'=>[$tpl,'',''],'error'=>2];
                }else{
                    //未关闭
                    return ['data'=>[$tpl,'',''],'error'=>1];
                }
            }else{
                return ['data'=>
                        [
                            substr($tpl,0,$before),
                            substr($tpl,$before,$pointer-$before+2),
                            substr($tpl,$pointer+2)
                        ],
                        'error'=>0
                ];
            }
        }
    }

    public static function wiki2xml(string $wiki):string{
        /**
         * 替换{{}} 到xml之前 要先把换行符号去掉
         */
        $wiki = str_replace("\n","",$wiki);

        /**
         * 把模版转换为xml
         */
        $remain = $wiki;
        $buffer = array();
        do {
            $arrWiki = MdRender::tplSplit($remain);
            $buffer[] = $arrWiki['data'][0];
            $tpl = $arrWiki['data'][1];
            if(!empty($tpl)){
                $pattern = "/\{\{(.+?)\|/";
                $replacement = '<MdTpl name="$1"><param>';
                $tpl = preg_replace($pattern,$replacement,$tpl);
                $tpl = str_replace("}}","</param></MdTpl>",$tpl);
                $tpl = str_replace("|","</param><param>",$tpl);
                /**
                 * 替换变量名
                 */

                $pattern = "/<param>([a-z]+?)=/";
                $replacement = '<param name="$1">';
                $tpl = preg_replace($pattern,$replacement,$tpl);
                $buffer[] = $tpl;
            }
            $remain = $arrWiki['data'][2];
        } while (!empty($remain));

        $html = implode('' , $buffer);

        $html = str_replace("<p>","<div>",$html);
        $html = str_replace("</p>","</div>",$html);
        $html = "<span>".$html."</span>";
        return $html;
    }
    public static function xmlQueryId(string $xml, string $id):string{
        try{
            $dom = simplexml_load_string($xml);
        }catch(\Exception $e){
            Log::error($e);
            return "<div></div>";
        }
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
        try{
            $dom = simplexml_load_string($xml);
        }catch(\Exception $e){
            Log::error($e);
            return $output;
        }
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
    public static function xml2tpl(string $xml, $channelId=[],$mode='read'):string{
        /**
         * 解析xml
         * 获取模版参数
         * 生成react 组件参数
         */
        try{
            $dom = simplexml_load_string($xml);
        }catch(\Exception $e){
            Log::error($e);
            Log::error($xml);
            return "<span>xml解析错误{$e}</span>";
        }

        if(!$dom){
            Log::error($xml);
            return "<span>xml解析错误</span>";
        }

        /*
        $doc = new \DOMDocument();
        $xml = str_replace('MdTpl','dfn',$xml);
        $ok = $doc->loadHTML($xml,LIBXML_HTML_NODEFDTD | LIBXML_DTDVALID);
        if(!$ok){
            return "<span>xml解析错误</span>";
        }
        */

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
                    $paramName = "";
                    foreach($param->attributes() as $pa => $pa_value){
                        if($pa === "name"){
                            $props["{$pa_value}"] = $param->__toString();
                            $paramName = $pa_value;
                        }
                    }
                    if(empty($paramName)){
                        $props["{$param_id}"] = $param->__toString();
                    }
                }
            }
            /**
             * 生成模版参数
             */
            $channelInfo = Channel::whereIn('uid',$channelId)->get();
            $tplRender = new TemplateRender($props,$channelInfo,$mode);
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

    public static function render2($markdown,$channelId=[],$queryId=null,$mode='read',$channelType,$contentType="markdown"){
        if(empty($markdown)){
            return "<span></span>";
        }
        $wiki = MdRender::markdown2wiki($markdown,$channelType,$contentType);
        $html = MdRender::wiki2xml($wiki);
        if(!is_null($queryId)){
            $html = MdRender::xmlQueryId($html, $queryId);
        }
        $tpl = MdRender::xml2tpl($html,$channelId,$mode);
        //生成可展开组件
        $tpl = str_replace("<div/>","<div></div>",$tpl);
        $pattern = '/<li><div>(.+?)<\/div><\/li>/';
        $replacement = '<li><MdTpl name="toggle" tpl="toggle" props=""><div>$1</div></MdTpl></li>';
        $tpl = preg_replace($pattern,$replacement,$tpl);
        return $tpl;
    }
    public static function markdown2wiki(string $markdown,$channelType,$contentType): string{
        //$markdown = mb_convert_encoding($markdown,'UTF-8','UTF-8');
        $markdown = iconv('UTF-8','UTF-8//IGNORE',$markdown);
        Log::info('nissaya');
        /**
         * nissaya
         * aaa=bbb\n
         * {{nissaya|aaa|bbb}}
         */
        if($channelType==='nissaya'){
            if($contentType === "json"){
                $json = json_decode($markdown);
                $nissayaWord = [];
                foreach ($json as $word) {
                    if(count($word->sn) === 1){
                        //只输出第一层级
                        $str = "{{nissaya|";
                        if(isset($word->word->value)){
                            $str .= $word->word->value;
                        }
                        $str .= "|";
                        if(isset($word->meaning->value)){
                            $str .= $word->meaning->value;
                        }
                        $str .= "}}";
                        $nissayaWord[] = $str;
                    }
                }
                $markdown = implode('',$nissayaWord);
            }else if($contentType === "markdown"){
                $lines = explode("\n",$markdown);
                $newLines = array();
                foreach ($lines as  $line) {
                    if(strstr($line,'=') === FALSE){
                        $newLines[] = $line;
                    }else{
                        $nissaya = explode('=',$line);
                        $meaning = array_slice($nissaya,1);
                        $meaning = implode('=',$meaning);
                        $newLines[] = "{{nissaya|{$nissaya[0]}|{$meaning}}}";
                    }
                }
                $markdown = implode("\n",$newLines);
            }
        }
        //$markdown = preg_replace("/\n\n/","<div></div>",$markdown);

                /**
         * 处理 mermaid
         */
        Log::info('mermaid');
        Log::info('mermaid:'.strpos($markdown,"```mermaid"));
        if(strpos($markdown,"```mermaid") !== FALSE){
            Log::info('has mermaid');
            $lines = explode("\n",$markdown);
            $newLines = array();
            $mermaidBegin = false;
            $mermaidString = array();
            foreach ($lines as  $line) {
                if($line === "```mermaid"){
                    Log::info('mermaidBegin');
                    $mermaidBegin = true;
                    $mermaidString = [];
                    continue;
                }
                if($mermaidBegin){
                    if($line === "```"){
                        Log::info('mermaid end');
                        $newLines[] = "{{mermaid|".base64_encode(\json_encode($mermaidString))."}}";
                        $mermaidBegin = false;
                    }else{
                        $mermaidString[] = $line;
                    }
                }else{
                    $newLines[] = $line;
                }
            }
            $markdown = implode("\n",$newLines);
        }

        /**
         * 替换换行符
         * react 无法处理 <br> 替换为<div></div>代替换行符作用
         */
        $markdown = str_replace('<br>','<div></div>',$markdown);

        /**
         * markdown -> html
         */
        Log::info('markdown -> html');
        $markdown = str_replace(['[[',']]'],['㐛','㐚'],$markdown);
        $html = Str::markdown($markdown);
        $html = str_replace(['㐛','㐚'],['[[',']]'],$html);
        $html = MdRender::fixHtml($html);

        #替换术语
        $pattern = "/\[\[(.+?)\]\]/";
        $replacement = '{{term|$1}}';
        $html = preg_replace($pattern,$replacement,$html);

        #替换句子模版
        $pattern = "/\{\{([0-9].+?)\}\}/";
        $replacement = '{{sent|$1}}';
        $html = preg_replace($pattern,$replacement,$html);

        #替换单行注释
        #<code>bla</code>
        #{{note|bla}}
        $pattern = '/<code>(.+?)<\/code>/';
        $replacement = '{{note|$1}}';
        $html = preg_replace($pattern,$replacement,$html);

        #替换多行注释
        #<pre><code>bla</code></pre>
        #{{note|bla}}
        $pattern = '/<pre><code>([\w\W]+?)<\/code><\/pre>/';
        $replacement = '{{note|$1}}';
        $html = preg_replace($pattern,$replacement,$html);


        return $html;
    }

    /**
     * string[] $channelId
     */
    public static function render($markdown,$channelId,$queryId=null,$mode='read',$channelType='translation',$contentType="markdown"){
        return MdRender::render2($markdown,$channelId,$queryId,$mode,$channelType,$contentType);
    }

    public static function  fixHtml($html) {
        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
        $html = mb_convert_encoding($html, 'HTML-ENTITIES', "UTF-8");
        $doc->loadHTML('<span>'.$html.'</span>',LIBXML_NOERROR  | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $fixed = $doc->saveHTML();
        $fixed = mb_convert_encoding($fixed, "UTF-8", 'HTML-ENTITIES');
        return $fixed;
    }
}
