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
                /*
                $pattern = '/(.+?)=(.+?)\n/';
                $replacement = '{{nissaya|$1|$2}}';
                $markdown = preg_replace($pattern,$replacement,$markdown);
                $pattern = '/(.+?)=(.?)\n/';
                $replacement = '{{nissaya|$1|$2}}';
                $markdown = preg_replace($pattern,$replacement,$markdown);
                $pattern = '/(.?)=(.+?)\n/';
                $replacement = '{{nissaya|$1|$2}}';
                $markdown = preg_replace($pattern,$replacement,$markdown);
                */
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
         * 替换换行符
         * react 无法处理 <br> 替换为<div></div>代替换行符作用
         */
        $markdown = str_replace('<br>','<div></div>',$markdown);

        /**
         * markdown -> html
         */
        $markdown = str_replace(['[[',']]'],['㐛','㐚'],$markdown);
        $html = Str::markdown($markdown);
        $html = str_replace(['㐛','㐚'],['[[',']]'],$html);

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

}
