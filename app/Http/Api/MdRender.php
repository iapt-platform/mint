<?php
namespace App\Http\Api;

use Illuminate\Support\Str;
use mustache\mustache;
use App\Models\DhammaTerm;
use App\Models\PaliText;
use App\Models\Channel;
use App\Http\Controllers\CorpusController;
use Illuminate\Support\Facades\Cache;
use App\Tools\RedisClusters;
use Illuminate\Support\Facades\Log;
use App\Tools\Markdown;

define("STACK_DEEP",8);

class MdRender{
    /**
     * 文字渲染模式
     * read 阅读模式
     * edit 编辑模式
     */
    protected $options = [
        'mode' => 'read',
        'channelType'=>'translation',
        'contentType'=>"markdown",
        'format'=>'react',
        'debug'=>[],
        'studioId'=>null,
        'lang'=>'zh-Hans',
        ];

    public function __construct($options=[])
    {
        foreach ($options as $key => $value) {
            $this->options[$key] = $value;
        }
    }

    /**
     * 将句子模版组成的段落复制一份，为了实现巴汉逐段对读
     */
    private function preprocessingForParagraph($input){
        if(!$this->options['paragraph']){
            return $input;
        }
        $paragraphs = explode("\n\n",$input);
        $output = [];
        foreach ($paragraphs as $key => $paragraph) {
            # 判断是否是纯粹的句子模版
            $pattern = "/\{\{sent\|id=([0-9].+?)\}\}/";
            $replacement = '';
            $space = preg_replace($pattern,$replacement,$paragraph);
            if(empty(trim($space))){
                $output[] = str_replace('}}','|text=origin}}',$paragraph);
                $output[] = str_replace('}}','|text=translation}}',$paragraph);
            }else{
                $output[] = $paragraph;
            }
        }

        return implode("\n\n",$output);
    }

    /**
     * 按照{{}}把字符串切分成三个部分。模版之前的，模版，和模版之后的
     */
    private function tplSplit($tpl){
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

    private function wiki2xml(string $wiki,$channelId=[]):string{
        /**
         * 把模版转换为xml
         */
        $remain = $wiki;
        $buffer = array();
        do {
            $arrWiki = $this->tplSplit($remain);
            $buffer[] = $arrWiki['data'][0];
            $tpl = $arrWiki['data'][1];
            if(!empty($tpl)){
                /**
                 * 处理模版 提取参数
                 */
                $tpl = str_replace("|\n","|",$tpl);
                $pattern = "/\{\{(.+?)\|/";
                $replacement = '<MdTpl class="tpl" name="$1"><param>';
                $tpl = preg_replace($pattern,$replacement,$tpl);
                $tpl = str_replace("}}","</param></MdTpl>",$tpl);
                $tpl = str_replace("|","</param><param>",$tpl);
                /**
                 * 替换变量名
                 */
                $pattern = "/<param>([a-z]+?)=/";
                $replacement = '<param name="$1">';
                $tpl = preg_replace($pattern,$replacement,$tpl);
                //tpl to react
                $tpl = str_replace('<param','<span class="param"',$tpl);
                $tpl = str_replace('</param>','</span>',$tpl);
                $tpl = $this->xml2tpl($tpl,$channelId);
                $buffer[] = $tpl;
            }
            $remain = $arrWiki['data'][2];
        } while (!empty($remain));

        $html = implode('' , $buffer);

        return $html;
    }
    private function xmlQueryId(string $xml, string $id):string{
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
    private function xml2tpl(string $xml, $channelId=[]):string{
        /**
         * 解析xml
         * 获取模版参数
         * 生成react 组件参数
         */
        try{
            //$dom = simplexml_load_string($xml);
            $doc = new \DOMDocument();
            $xml = str_replace('MdTpl','dfn',$xml);
            $xml = mb_convert_encoding($xml, 'HTML-ENTITIES', "UTF-8");
            $ok = $doc->loadHTML($xml,LIBXML_NOERROR  | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        }catch(\Exception $e){
            Log::error($e);
            Log::error($xml);
            return "<span>xml解析错误{$e}</span>";
        }

        if(!$ok){
            return "<span>xml解析错误</span>";
        }
        /*
        if(!$dom){
            Log::error($xml);
            return "<span>xml解析错误</span>";
        }
         */

        //$tpl_list = $dom->xpath('//MdTpl');
        $tpl_list = $doc->getElementsByTagName('dfn');

        foreach ($tpl_list as $key => $tpl) {
            /**
             * 遍历 MdTpl 处理参数
             */
            $props = [];
            $tpl_name = '';
            foreach($tpl->attributes as $a => $a_value){
                if($a_value->nodeName==="name"){
                    $tpl_name = $a_value->nodeValue;
                    break;
                }
            }
            $param_id = 0;
            $child = $tpl->firstChild;
            while ($child) {
                # 处理每个参数
                if($child->nodeName === "span"){
                    $param_id++;
                    $paramName = "";
                    foreach($child->attributes as $pa => $pa_value){
                        if($pa_value->nodeName === "name"){
                            $nodeText = $pa_value->nodeValue;
                            $props["{$nodeText}"] = $child->nodeValue;
                            $paramName = $pa_value;
                        }
                    }
                    if(empty($paramName)){
                        foreach ($child->childNodes as $param_child) {
                            # code...
                            if($param_child->nodeType ===3){
                                $props["{$param_id}"] = $param_child->nodeValue;
                            }
                        }

                    }
                }
                $child = $child->nextSibling;
            }
            /**
             * 生成模版参数
             *
             */
            //TODO 判断$channelId里面的是否都是uuid
            $channelInfo = [];
            foreach ($channelId as $key => $id) {
                $channelInfo[] = Channel::where('uid',$id)->first();
            }
            $tplRender = new TemplateRender($props,
                                        $channelInfo,
                                        $this->options['mode'],
                                        $this->options['format'],
                                        $this->options['studioId'],
                                        $this->options['debug'],
                                        $this->options['lang'],
                                    );

            $tplProps = $tplRender->render($tpl_name);
            if($this->options['format']==='react' && $tplProps){
                $props = $doc->createAttribute("props");
                $props->nodeValue = $tplProps['props'];
                $tpl->appendChild($props);
                $attTpl = $doc->createAttribute("tpl");
                $attTpl->nodeValue = $tplProps['tpl'];
                $tpl->appendChild($attTpl);
                $htmlElement = $doc->createElement($tplProps['tag']);
                $htmlElement->nodeValue=$tplProps['html'];
                $tpl->appendChild($htmlElement);
            }
        }
        $html = $doc->saveHTML();
        $html = str_replace(['<dfn','</dfn>'],['<MdTpl','</MdTpl>'],$html);
        switch ($this->options['format']) {
            case 'react':
                return trim($html);
                break;
            case 'unity':
                if($tplProps){
                    return "{{"."{$tplProps['tpl']}|{$tplProps['props']}"."}}";
                }else{
                    return '';
                }
                break;
            case 'html':
                if(isset($tplProps)){
                    if(is_array($tplProps)){
                        return '';
                    }else{
                        return $tplProps;
                    }
                }else{
                    Log::error('tplProps undefine');
                    return '';
                }
                break;
            case 'text':
            case 'simple':
                if(isset($tplProps)){
                    if(is_array($tplProps)){
                        return '';
                    }else{
                        return $tplProps;
                    }
                }else{
                    Log::error('tplProps undefine');
                    return '';
                }
                break;
            case 'tex':
                if(isset($tplProps)){
                    if(is_array($tplProps)){
                        return '';
                    }else{
                        return $tplProps;
                    }
                }else{
                    Log::error('tplProps undefine');
                    return '';
                }
                break;
            default:
                return '';
                break;
        }
    }


    private function markdown2wiki(string $markdown): string{
        //$markdown = mb_convert_encoding($markdown,'UTF-8','UTF-8');
        $markdown = iconv('UTF-8','UTF-8//IGNORE',$markdown);
        /**
         * nissaya
         * aaa=bbb\n
         * {{nissaya|aaa|bbb}}
         */
        if($this->options['channelType']==='nissaya'){
            if($this->options['contentType'] === "json"){
                $json = json_decode($markdown);
                $nissayaWord = [];
                if(is_array($json)){
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
                }else{
                    Log::error('json data is not array',['data'=>$markdown]);
                }

                $markdown = implode('',$nissayaWord);
            }else if($this->options['contentType'] === "markdown"){
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
        if(strpos($markdown,"```mermaid") !== false){
            $lines = explode("\n",$markdown);
            $newLines = array();
            $mermaidBegin = false;
            $mermaidString = array();
            foreach ($lines as  $line) {
                if($line === "```mermaid"){
                    $mermaidBegin = true;
                    $mermaidString = [];
                    continue;
                }
                if($mermaidBegin){
                    if($line === "```"){
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
        //$markdown = str_replace('<br>','<div></div>',$markdown);

        /**
         * markdown -> html
         */
        /*

        $html = MdRender::fixHtml($html);
        */

        #替换术语
        $pattern = "/\[\[(.+?)\]\]/";
        $replacement = '{{term|$1}}';
        $markdown = preg_replace($pattern,$replacement,$markdown);

        #替换句子模版
        $pattern = "/\{\{([0-9].+?)\}\}/";
        $replacement = '{{sent|$1}}';
        $markdown = preg_replace($pattern,$replacement,$markdown);

        /**
         * 替换多行注释
         * ```
         * bla
         * bla
         * ```
         * {{note|
         * bla
         * bla
         * }}
         */
        if(strpos($markdown,"```\n") !== false){
            $lines = explode("\n",$markdown);
            $newLines = array();
            $noteBegin = false;
            $noteString = array();
            foreach ($lines as  $line) {

                if($noteBegin){
                    if($line === "```"){
                        $newLines[] = "}}";
                        $noteBegin = false;
                    }else{
                        $newLines[] = $line;
                    }
                }else{
                    if($line === "```"){
                        $noteBegin = true;
                        $newLines[] = "{{note|";
                        continue;
                    }else{
                       $newLines[] = $line;
                    }
                }
            }
            if($noteBegin){
                $newLines[] = "}}";
            }
            $markdown = implode("\n",$newLines);
        }

        /**
         * 替换单行注释
         * `bla bla`
         * {{note|bla}}
         */
        $pattern = "/`(.+?)`/";
        $replacement = '{{note|$1}}';
        $markdown = preg_replace($pattern,$replacement,$markdown);

        return $markdown;
    }

    private function markdownToHtml($markdown){
        $markdown = str_replace('MdTpl','mdtpl',$markdown);
        $markdown = str_replace(['<param','</param>'],['<span','</span>'],$markdown);

        $html = Markdown::render($markdown);
        if($this->options['format']==='react'){
            $html = $this->fixHtml($html);
        }
        $html = str_replace('<hr>','<hr />',$html);
        //给H1-6 添加uuid
        for ($i=1; $i<7 ; $i++) {
            if(strpos($html,"<h{$i}>")===false){
                continue;
            }
            $output = array();
            $input = $html;
            $hPos = strpos($input,"<h{$i}>");
            while ($hPos !== false) {
                $output[] = substr($input,0,$hPos);
                $output[] = "<h{$i} id='".Str::uuid()."'>";
                $input = substr($input,$hPos+4);
                $hPos = strpos($input,"<h{$i}>");
            }
            $output[] = $input;
            $html = implode('',$output);
        }
        $html = str_replace('mdtpl','MdTpl',$html);
        return $html;
    }
    private function  fixHtml($html) {
        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
        $html = mb_convert_encoding($html, 'HTML-ENTITIES', "UTF-8");
        $doc->loadHTML('<span>'.$html.'</span>',LIBXML_NOERROR  | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $fixed = $doc->saveHTML();
        $fixed = mb_convert_encoding($fixed, "UTF-8", 'HTML-ENTITIES');
        return $fixed;
    }
    public static function init(){
        $GLOBALS["MdRenderStack"] = 0;
    }
    public function convert($markdown,$channelId=[],$queryId=null){
        if(isset($GLOBALS["MdRenderStack"]) && is_numeric($GLOBALS["MdRenderStack"])){
            $GLOBALS["MdRenderStack"]++;
        }else{
            $GLOBALS["MdRenderStack"] = 1;
        }
        if($GLOBALS["MdRenderStack"]<3){
            $output  =  $this->_convert($markdown,$channelId,$queryId);
        }else{
            $output  = $markdown;
        }
        $GLOBALS["MdRenderStack"]--;
        return $output;
    }
    private function _convert($markdown,$channelId=[],$queryId=null){
        if(empty($markdown)){
            switch ($this->options['format']) {
                case 'react':
                    return "<span></span>";
                    break;
                default:
                    return "";
                    break;
            }
        }
        $wiki = $this->markdown2wiki($markdown);
        $html = $this->wiki2xml($wiki,$channelId);
        if(!is_null($queryId)){
            $html = $this->xmlQueryId($html, $queryId);
        }
        $html = $this->markdownToHtml($html);

        //后期处理
        $output = '';
        switch ($this->options['format']) {
            case 'react':
                //生成可展开组件
                $html = str_replace("<div/>","<div></div>",$html);
                $pattern = '/<li><div>(.+?)<\/div><\/li>/';
                $replacement = '<li><MdTpl name="toggle" tpl="toggle" props=""><div>$1</div></MdTpl></li>';
                $output = preg_replace($pattern,$replacement,$html);
                break;
            case 'text':
            case 'simple':
                $html = strip_tags($html);
                $output = htmlspecialchars_decode($html,ENT_QUOTES);
                //$output = html_entity_decode($html);
                break;
            case 'tex':
                $html = strip_tags($html);
                $output = htmlspecialchars_decode($html,ENT_QUOTES);
                //$output = html_entity_decode($html);
                break;
            case 'unity':
                $html = str_replace(['<strong>','</strong>','<em>','</em>'],['[%b%]','[%/b%]','[%i%]','[%/i%]'],$html);
                $html = strip_tags($html);
                $html = str_replace(['[%b%]','[%/b%]','[%i%]','[%/i%]'],['<b>','</b>','<i>','</i>'],$html);
                $output = htmlspecialchars_decode($html,ENT_QUOTES);
                break;
            case 'html':
                $output = htmlspecialchars_decode($html,ENT_QUOTES);
                //处理脚注
                if($this->options['footnote'] && isset($GLOBALS['note']) && count($GLOBALS['note'])>0){
                    $output .= '<div><h1>endnote</h1>';
                    foreach ($GLOBALS['note'] as $footnote) {
                        $output .= '<p><a name="footnote-'.$footnote['sn'].'">['.$footnote['sn'].']</a> '.$footnote['content'].'</p>';
                    }
                    $output .= '</div>';
                    unset($GLOBALS['note']);
                }
                //处理图片链接
                $output = str_replace('<img src="','<img src="'.config('app.url'),$output);
                break;
            case 'markdown':
                $output = $markdownWithTpl;
                break;
        }
        return $output;
    }


    /**
     * string[] $channelId
     */
    public static function render($markdown,$channelId,$queryId=null,$mode='read',$channelType='translation',$contentType="markdown",$format='react'){

            $mdRender = new MdRender(
                            [
                                'mode'=>$mode,
                                'channelType'=>$channelType,
                                'contentType'=>$contentType,
                                'format'=>$format
                            ]);

            $output  = $mdRender->convert($markdown,$channelId,$queryId);

        return $output;
    }


}
