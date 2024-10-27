<?php

namespace App\Http\Controllers;

use Carbon\Carbon;

use App\Models\Sentence;
use App\Models\Channel;
use App\Models\PaliText;
use App\Models\WbwTemplate;
use App\Models\WbwBlock;
use App\Models\Wbw;
use App\Models\Discussion;
use App\Models\PaliSentence;
use App\Models\SentSimIndex;
use App\Models\CustomBookSentence;
use App\Models\CustomBook;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Tools\RedisClusters;
use App\Http\Api\MdRender;
use App\Http\Api\SuggestionApi;
use App\Http\Api\ChannelApi;
use App\Http\Api\UserApi;
use App\Http\Api\StudioApi;
use App\Http\Api\AuthApi;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;
use App\Http\Resources\TocResource;
use Illuminate\Support\Facades\Redis;

class CorpusController extends Controller
{
    protected $result = [
        "uid"=> '',
        "title"=> '',
        "path"=>[],
        "sub_title"=> '',
        "summary"=> '',
        "content"=> '',
        "content_type"=> "html",
        "toc" => [],
        "status"=>30,
        "lang"=> "",
        "created_at"=> "",
        "updated_at"=> "",
    ];
    protected $wbwChannels = [];
    //句子需要查询的列
    protected $selectCol = [
        'uid',
        'book_id',
        'paragraph',
        'word_start',
        "word_end",
        'channel_uid',
        'content',
        'content_type',
        'editor_uid',
        'acceptor_uid',
        'pr_edit_at',
        'fork_at',
        'create_time',
        'modify_time',
        'created_at',
        'updated_at',
    ];

    protected $userUuid=null;

    protected $debug=[];

    public function __construct()
    {


    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        switch ($request->get('view')) {
            case 'para':
                return $this->showPara($request);
                break;
            default:
                # code...
                break;
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Sentence  $sentence
     * @return \Illuminate\Http\Response
     */
    public function show(Sentence $sentence)
    {
        //
    }
    public function getSentTpl($id,$channels,$mode='edit',$onlyProps=false,$format='react'){
        $sent = [];
        $sentId = \explode('-',$id);
        if(count($sentId) !== 4){
            return false;
        }
        $bookId = (int)$sentId[0];
        if( $bookId < 1000){
            if($mode==='read'){
                $channelId = ChannelApi::getSysChannel('_System_Pali_VRI_');
            }else{
                $channelId = ChannelApi::getSysChannel('_System_Wbw_VRI_');
            }
        }else{
            $channelId = CustomBook::where('book_id',$bookId)->value('channel_id');
        }


        if(isset($channelId) && $channelId){
            array_push($channels,$channelId);
        }
        $record = Sentence::select($this->selectCol)
                        ->where('book_id',$sentId[0])
                        ->where('paragraph',$sentId[1])
                        ->where('word_start',(int)$sentId[2])
                        ->where('word_end',(int)$sentId[3])
                        ->whereIn('channel_uid',$channels)
                        ->get();

        $channelIndex = $this->getChannelIndex($channels);

        if(isset($toSentFormat)){
            foreach ($toSentFormat as $key => $org) {
                $record[] = $org;
            }
        }

        //获取wbw channel
        //目前默认的 wbw channel 是第一个translation channel
        foreach ($channels as  $channel) {
            # code...
            if($channelIndex[$channel]->type==='translation'){
                $this->wbwChannels[] = $channel;
                break;
            }
        }
        return $this->makeContent($record,$mode,$channelIndex,[],$onlyProps,false,$format);
    }
    /**
     * Display the specified resource.
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function showSent(Request  $request, string $id)
    {
        $user = AuthApi::current($request);
        if($user){
            $this->userUuid = $user['user_uid'];
        }
        $channels = \explode('_',$request->get('channels'));

        $this->result['uid'] = "";
        $this->result['title'] = "";
        $this->result['subtitle'] = "";
        $this->result['summary'] = "";
        $this->result['lang'] = "";
        $this->result['status'] = 30;
        $this->result['content'] = $this->getSentTpl($id,$channels,$request->get('mode','edit'));
        return $this->ok($this->result);
    }
    /**
     * 获取某句子的全部译文

     * @param  \Illuminate\Http\Request  $request
     * @param string $type
     * @param string $id
     * @return \Illuminate\Http\Response
     */
    public function showSentences(Request $request, string $type, string $id){
        $user = AuthApi::current($request);
        if($user){
            $this->userUuid = $user['user_uid'];
        }

        $param = \explode('_',$id);
        $sentId = \explode('-',$param[0]);
        $channels = [];

		#获取channel类型
        $sentChannel = Sentence::select('channel_uid')
                    ->where('book_id',$sentId[0])
                    ->where('paragraph',$sentId[1])
                    ->where('word_start',$sentId[2])
                    ->where('word_end',$sentId[3])
                    ->get();
        foreach ($sentChannel as $key => $value) {
            # code...
            $channels[] = $value->channel_uid;
        }
		$channelInfo = Channel::whereIn("uid",$channels)->select(['uid','type','lang','name'])->get();
		$indexChannel = [];
        $channels = [];
		foreach ($channelInfo as $key => $value) {
			# code...
            if($value->type === $type){
                $indexChannel[$value->uid] = $value;
                $channels[] = $value->uid;
            }
		}
		//获取句子数据
        $record = Sentence::select($this->selectCol)
                    ->where('book_id',$sentId[0])
                    ->where('paragraph',$sentId[1])
                    ->where('word_start',$sentId[2])
                    ->where('word_end',$sentId[3])
                    ->whereIn('channel_uid',$channels)
                    ->orderBy('paragraph')
                    ->orderBy('word_start')
                    ->get();
        if(count($record) ===0){
            return $this->error("no data");
        }

        $this->result['uid'] = "";
        $this->result['title'] = "";
        $this->result['subtitle'] = "";
        $this->result['summary'] = "";
        $this->result['lang'] = "";
        $this->result['status'] = 30;
        $this->result['content'] = $this->makeContent($record,$mode,$indexChannel);
        return $this->ok($this->result);
    }
    /**
     * Store a newly created resource in storage.

     * @param  \Illuminate\Http\Request  $request
     * @param string $id
     * @param string $mode
     * @return \Illuminate\Http\Response
     */
    public function showPara(Request $request)
    {
        if($request->has('debug')){
            $this->debug = explode(',',$request->get('debug'));
        }
        $user = AuthApi::current($request);
        if($user){
            $this->userUuid = $user['user_uid'];
        }
        //
        $channels = [];
        if($request->get('mode') === 'edit'){
            //翻译模式加载json格式原文
            $channels[] = ChannelApi::getSysChannel('_System_Wbw_VRI_');
        }else{
            //阅读模式加载html格式原文
            $channels[] = ChannelApi::getSysChannel('_System_Pali_VRI_');
        }

        if($request->has('channels')){
            if(strpos($request->get('channels'),',') === FALSE){
                $getChannel = explode('_',$request->get('channels'));
            }else{
                $getChannel = explode(',',$request->get('channels'));
            }
            $channels = array_merge($channels,$getChannel );
        }
        $para = explode(",",$request->get('par'));

        //段落所在章节
        $parent = PaliText::where('book',$request->get('book'))
                            ->where('paragraph',$para[0])->first();
        $chapter = PaliText::where('book',$request->get('book'))
                            ->where('paragraph',$parent->parent)->first();
        if($chapter){
            if(empty($chapter->toc)){
                $this->result['title'] = "unknown";
            }else{
                $this->result['title'] = $chapter->toc;
                $this->result['sub_title'] = $chapter->toc;
                $this->result['path'] = json_decode($parent->path);
            }
        }
        $paraFrom = $para[0];
        $paraTo = end($para);

        $indexedHeading = [];

		#获取channel索引表
        $tranChannels = [];
		$channelInfo = Channel::whereIn("uid",$channels)
                        ->select(['uid','type','lang','name'])->get();
		foreach ($channelInfo as $key => $value) {
			# code...
            if($value->type==="translation" ){
                $tranChannels[] = $value->uid;
            }
		}
        $indexChannel = [];
        $indexChannel = $this->getChannelIndex($channels);
        //获取wbw channel
        //目前默认的 wbw channel 是第一个translation channel
        foreach ($channels as $key => $value) {
            # code...
            if(isset($indexChannel[$value]) &&
                 $indexChannel[$value]->type==='translation'){
                $this->wbwChannels[] = $value;
                break;
            }
        }
        //章节译文标题
        $title = Sentence::select($this->selectCol)
                    ->where('book_id',$parent->book)
                    ->where('paragraph',$parent->parent)
                    ->whereIn('channel_uid',$tranChannels)
                    ->first();
        if($title){
            $this->result['title'] = MdRender::render($title->content,[$title->channel_uid]);
        }

        /**
         * 获取句子数据
         */
        $record = Sentence::select($this->selectCol)
                    ->where('book_id',$request->get('book'))
                    ->whereIn('paragraph',$para)
                    ->whereIn('channel_uid',$channels)
                    ->orderBy('paragraph')
                    ->orderBy('word_start')
                    ->get();
        if(count($record) ===0){
            $this->result['content'] = "<span>No Data</span>";
        }else{
            $this->result['content'] = $this->makeContent($record,$request->get('mode','read'),$indexChannel,$indexedHeading,false,true);
        }

        return $this->ok($this->result);
    }
    /**
     * Store a newly created resource in storage.

     * @param  \Illuminate\Http\Request  $request
     * @param string $id
     * @return \Illuminate\Http\Response
     */
    public function showChapter(Request $request, string $id)
    {
        if($request->has('debug')){
            $this->debug = explode(',',$request->get('debug'));
        }
        $user = AuthApi::current($request);
        if($user){
            $this->userUuid = $user['user_uid'];
        }
        //
        $sentId = \explode('-',$id);
        $channels = [];
        if($request->has('channels')){
            if(strpos($request->get('channels'),',') === FALSE){
                $channels = explode('_',$request->get('channels'));
            }else{
                $channels = explode(',',$request->get('channels'));
            }
        }
        $mode = $request->get('mode','read');
        if($mode === 'read'){
            //阅读模式加载html格式原文
            $channelId = ChannelApi::getSysChannel('_System_Pali_VRI_');
        }else{
            //翻译模式加载json格式原文
            $channelId = ChannelApi::getSysChannel('_System_Wbw_VRI_');
        }

        if($channelId !== false){
            $channels[] = $channelId;
        }

        $chapter = PaliText::where('book',$sentId[0])->where('paragraph',$sentId[1])->first();
        if(!$chapter){
            return $this->error("no data");
        }
        $paraFrom = $sentId[1];
        $paraTo = $sentId[1]+$chapter->chapter_len-1;

        if(empty($chapter->toc)){
            $this->result['title'] = "unknown";
        }else{
            $this->result['title'] = $chapter->toc;
            $this->result['sub_title'] = $chapter->toc;
            $this->result['path'] = json_decode($chapter->path);
        }

        //获取标题
        $heading = PaliText::select(["book","paragraph","level"])
                            ->where('book',$sentId[0])
                            ->whereBetween('paragraph',[$paraFrom,$paraTo])
                            ->where('level','<',8)
                            ->get();
        //将标题段落转成索引数组 以便输出标题层级
        $indexedHeading = [];
        foreach ($heading as $key => $value) {
            # code...
            $indexedHeading["{$value->book}-{$value->paragraph}"] = $value->level;
        }
		#获取channel索引表
        $tranChannels = [];
		$channelInfo = Channel::whereIn("uid",$channels)->select(['uid','type','lang','name'])->get();
		foreach ($channelInfo as $key => $value) {
			# code...
            if($value->type === "translation" ){
                $tranChannels[] = $value->uid;
            }
		}
        $indexChannel = [];
        $indexChannel = $this->getChannelIndex($channels);
        //获取wbw channel
        //目前默认的 wbw channel 是第一个translation channel
        //TODO 处理不存在的channel id
        foreach ($channels as $key => $value) {
            # code...
            if(isset($indexChannel[$value]) &&
                $indexChannel[$value]->type==='translation'){
                $this->wbwChannels[] = $value;
                break;
            }
        }
        $title = Sentence::select($this->selectCol)
                    ->where('book_id',$sentId[0])
                    ->where('paragraph',$sentId[1])
                    ->whereIn('channel_uid',$tranChannels)
                    ->first();
        if($title){
            $this->result['title'] = MdRender::render($title->content,[$title->channel_uid]);
            $mdRender = new MdRender(['format'=>'simple']);
            $this->result['title_text'] = $mdRender->convert($title->content,[$title->channel_uid]);
        }

        /**
         * 获取句子数据
         * 算法：
         * 1. 如果标题和下一级第一个标题之间有段落。只输出这些段落和子目录
         * 2. 如果标题和下一级第一个标题之间没有间隔 且 chapter 长度大于10000个字符 且有子目录，只输出子目录
         * 3. 如果二者都不是，lazy load
         */
		//1. 计算 标题和下一级第一个标题之间 是否有间隔
        $nextChapter =  PaliText::where('book',$sentId[0])
                                ->where('paragraph',">",$sentId[1])
                                ->where('level','<',8)
                                ->orderBy('paragraph')
                                ->value('paragraph');
        $between = $nextChapter - $sentId[1];
        //查找子目录
        $chapterLen = $chapter->chapter_len;
        $toc = PaliText::where('book',$sentId[0])
                        ->whereBetween('paragraph',[$paraFrom+1,$paraFrom+$chapterLen-1])
                        ->where('level','<',8)
                        ->orderBy('paragraph')
                        ->select(['book','paragraph','level','toc'])
                        ->get();
        $maxLen = 3000;
        if($between > 1){
            //有间隔
            $paraTo = $nextChapter - 1;
        }else{
            if($chapter->chapter_strlen > $maxLen){
                if(count($toc)>0){
                    //有子目录只输出标题和目录
                    $paraTo = $paraFrom;
                }else{
                    //没有子目录 全部输出
                }
            }else{
                //章节小。全部输出 不输出子目录
                $toc = [];
            }
        }

        $pFrom = $request->get('from',$paraFrom);
        $pTo = $request->get('to',$paraTo);
        //根据句子的长度找到这次应该加载的段落

        $paliText = PaliText::select(['paragraph','lenght'])
                            ->where('book',$sentId[0])
                            ->whereBetween('paragraph',[$pFrom,$pTo])
                            ->orderBy('paragraph')
                            ->get();
        $sumLen = 0;
        $currTo = $pTo;
        foreach ($paliText as $para) {
            $sumLen += $para->lenght;
            if($sumLen > $maxLen){
                $currTo = $para->paragraph;
                break;
            }
        }
        $record = Sentence::select($this->selectCol)
                          ->where('book_id',$sentId[0])
                          ->whereBetween('paragraph',[$pFrom,$currTo])
                          ->whereIn('channel_uid',$channels)
                          ->orderBy('paragraph')
                          ->orderBy('word_start')
                          ->get();
        if(count($record) ===0){
            return $this->error("no data");
        }
        $this->result['content'] = $this->makeContent($record,$mode,$indexChannel,$indexedHeading,false,true);
        if(!$request->has('from')){
            //第一次才显示toc
            $this->result['toc'] = TocResource::collection($toc);
        }
        if($currTo < $pTo){
            $this->result['from'] = $currTo+1;
            $this->result['to'] = $pTo;
            $this->result['paraId'] = $id;
            $this->result['channels'] = $request->get('channels');
            $this->result['mode'] = $request->get('mode');
        }

        return $this->ok($this->result);
    }

    private function getChannelIndex($channels,$type=null){
        #获取channel索引表
        $channelInfo = Channel::whereIn("uid",$channels)
                        ->select(['uid','type','name','lang','owner_uid'])
                        ->get();
        $indexChannel = [];
        foreach ($channels as $key => $channelId) {
            $channelInfo = Channel::where("uid",$channelId)
                        ->select(['uid','type','name','lang','owner_uid'])->first();
            if(!$channelInfo){
                Log::error('no channel id'.$channelId);
                continue;
            }
            if($type !== null && $channelInfo->type !== $type){
                continue;
            }
            $indexChannel[$channelId] = $channelInfo;
            $indexChannel[$channelId]->studio = StudioApi::getById($channelInfo->owner_uid);
        }
        return $indexChannel;
    }
    /**
     * 根据句子库数据生成文章内容
     * $record 句子数据
     * $mode read | edit | wbw
     * $indexChannel channel索引
     * $indexedHeading 标题索引 用于给段落加标题标签 <h1> ect.
     */
    private function makeContent($record,$mode,$indexChannel,$indexedHeading=[],$onlyProps=false,$paraMark=false,$format='react'){
        $content = [];
		$lastSent = "0-0";
		$sentCount = 0;
        $sent = [];
        $sent["origin"] = [];
        $sent["translation"] = [];

        //获取句子编号列表
        $sentList = [];
        foreach ($record as $key => $value) {
            $currSentId = "{$value->book_id}-{$value->paragraph}-{$value->word_start}-{$value->word_end}";
            $sentList[$currSentId]=[$value->book_id ,$value->paragraph,$value->word_start,$value->word_end];
            $value->sid = "{$currSentId}_{$value->channel_uid}";
        }
        $channelsId = array();
        $count = 0;
        foreach ($indexChannel as $channelId => $info){
            if($count>0){
                $channelsId[] = $channelId;
            }
            $count++;
        }
        //遍历列表查找每个句子的所有channel的数据，并填充
        $currPara = "";
        foreach ($sentList as $currSentId => $arrSentId) {
            $para = $arrSentId[0]."-".$arrSentId[1];
            if($currPara !== $para){
                $currPara = $para;
                //输出段落标记

                if($paraMark){
                    $sentInPara = array();
                    foreach ($sentList as $sentId => $sentParam) {
                        if($sentParam[0]===$arrSentId[0] &&
                           $sentParam[1]===$arrSentId[1]){
                            $sentInPara[] = $sentId;
                        }
                    }

                    //输出段落起始
                    if(!empty($currPara)){
                        $content[] = '</MdTpl>';
                    }
                    $markProps = base64_encode(\json_encode([
                            'book'=>$arrSentId[0],
                            'para'=>$arrSentId[1],
                            'channels'=>$channelsId,
                            'sentences'=>$sentInPara,
                            'mode'=>$mode,
                        ])) ;
                    $content[] = "<MdTpl tpl='para-shell' props='{$markProps}' >";
                }

            }
            $sent = $this->newSent($arrSentId[0],$arrSentId[1],$arrSentId[2],$arrSentId[3]);
            foreach ($indexChannel as $channelId => $info) {
                # code...
                $sid = "{$currSentId}_{$channelId}";
                if(isset($info->studio)){
                    $studioInfo = $info->studio;
                }else{
                    $studioInfo = null;
                }
                $newSent = [
                    "content"=>"",
                    "html"=> "",
                    "book"=> $arrSentId[0],
                    "para"=> $arrSentId[1],
                    "wordStart"=> $arrSentId[2],
                    "wordEnd"=> $arrSentId[3],
                    "channel"=> [
                        "name"=>$info->name,
                        "type"=>$info->type,
                        "id"=> $info->uid,
                        'lang' => $info->lang,
                    ],
                    "studio" => $studioInfo,
                    "updateAt"=> "",
                    "suggestionCount" => SuggestionApi::getCountBySent($arrSentId[0],$arrSentId[1],$arrSentId[2],$arrSentId[3],$channelId),
                ];

                $row = Arr::first($record,function($value,$key) use($sid){
                    return $value->sid===$sid;
                });
                if($row){
                    $newSent['id'] = $row->uid;
                    $newSent['content'] = $row->content;
                    $newSent['contentType'] = $row->content_type;
                    $newSent['html'] = '';
                    $newSent["editor"]=UserApi::getByUuid($row->editor_uid);
                    /**
                     * TODO 刷库改数据
                     * 旧版api没有更新updated_at所以造成旧版的数据updated_at数据比modify_time 要晚
                     */
                    $newSent['forkAt'] =  $row->fork_at; //
                    $newSent['updateAt'] =  $row->updated_at; //
                    $newSent['updateAt'] = date("Y-m-d H:i:s.",$row->modify_time/1000).($row->modify_time%1000)." UTC";

                    $newSent['createdAt'] = $row->created_at;
                    if($mode !== "read"){
                        if(isset($row->acceptor_uid) && !empty($row->acceptor_uid)){
                            $newSent["acceptor"]=UserApi::getByUuid($row->acceptor_uid);
                            $newSent["prEditAt"]=$row->pr_edit_at;
                        }
                    }
                    switch ($info->type) {
                        case 'wbw':
                        case 'original':
                            //
                            // 在编辑模式下。
                            // 如果是原文，查看是否有逐词解析数据，
                            // 有的话优先显示。
                            // 阅读模式直接显示html原文
                            // 传过来的数据一定有一个原文channel
                            //
                            if($mode === "read"){
                                $newSent['content'] = "";
                                $newSent['html'] = MdRender::render($row->content,[$row->channel_uid],
                                                                            null,$mode,"translation",
                                                                            $row->content_type,$format);
                            }else{
                                if($row->content_type==='json'){
                                    $newSent['channel']['type'] = "wbw";
                                    if(isset($this->wbwChannels[0])){
                                        $newSent['channel']['name'] = $indexChannel[$this->wbwChannels[0]]->name;
                                        $newSent['channel']['lang'] = $indexChannel[$this->wbwChannels[0]]->lang;
                                        $newSent['channel']['id'] = $this->wbwChannels[0];
                                        //存在一个translation channel
                                        //尝试查找逐词解析数据。找到，替换现有数据
                                        $wbwData = $this->getWbw($arrSentId[0],$arrSentId[1],$arrSentId[2],$arrSentId[3],
                                                                $this->wbwChannels[0]);
                                        if($wbwData){
                                            $newSent['content'] = $wbwData;
                                            $newSent['contentType'] = 'json';
                                            $newSent['html'] = "";
                                            $newSent['studio'] = $indexChannel[$this->wbwChannels[0]]->studio;
                                        }
                                    }
                                }else{
                                    $newSent['content'] = $row->content;
                                    $newSent['html'] = MdRender::render($row->content,[$row->channel_uid],
                                                                                null,$mode,"translation",
                                                                                $row->content_type,$format);
                                }
                            }

                            break;
                        case 'nissaya':
                            $newSent['html'] = RedisClusters::remember("/sent/{$channelId}/{$currSentId}/{$format}",
                                                config('mint.cache.expire'),
                                                function() use($row,$mode,$format){
                                                    return MdRender::render($row->content,[$row->channel_uid],
                                                                            null,$mode,"nissaya",
                                                                            $row->content_type,$format);
                                                });
                            break;
                        default:
                            $options = [
                                    'debug'=>$this->debug,
                                    'format'=>$format,
                                    'mode'=>$mode,
                                    'channelType'=>'translation',
                                    'contentType'=>$row->content_type,
                            ];
                            $mdRender = new MdRender($options);
                            $newSent['html'] = $mdRender->convert($row->content,[$row->channel_uid]);
                            Log::debug('md render',['content'=>$row->content,'options'=>$options,'render'=>$newSent['html']]);
                            break;
                    }
                }
                switch ($info->type) {
                    case 'wbw':
                    case 'original':
                        array_push($sent["origin"],$newSent);
                        break;
                    default:
                        array_push($sent["translation"],$newSent);
                        break;
                }
            }
            if($onlyProps){
                return $sent;
            }
            $content = $this->pushSent($content,$sent,0,$mode);
        }
        if($paraMark){
            $content[] = '</MdTpl>';
        }
        $output = \implode("",$content);
        return "<div>{$output}</div>";
    }
    public function getWbw($book,$para,$start,$end,$channel){
        /**
         * 非阅读模式下。原文使用逐词解析数据。
         * 优先加载第一个translation channel 如果没有。加载默认逐词解析。
         */

        //获取逐词解析数据
        $wbwBlock = WbwBlock::where('channel_uid',$channel)
                            ->where('book_id',$book)
                            ->where('paragraph',$para)
                            ->select('uid')
                            ->first();
        if(!$wbwBlock){
            return false;
        }
        //找到逐词解析数据
        $wbwData = Wbw::where('block_uid',$wbwBlock->uid)
                      ->whereBetween('wid',[$start,$end])
                      ->select(['book_id','paragraph','wid','data','uid','editor_id','created_at','updated_at'])
                      ->orderBy('wid')
                      ->get();
        $wbwContent = [];
        foreach ($wbwData as $wbwrow) {
            $wbw = str_replace("&nbsp;",' ',$wbwrow->data);
            $wbw = str_replace("<br>",' ',$wbw);

            $xmlString = "<root>" . $wbw . "</root>";
            try{
                $xmlWord = simplexml_load_string($xmlString);
            }catch(Exception $e){
                continue;
            }
            $wordsList = $xmlWord->xpath('//word');
            foreach ($wordsList as $word) {
                $case = \str_replace(['#','.'],['$',''],$word->case->__toString());
                $case = \str_replace('$$','$',$case);
                $case = trim($case);
                $case = trim($case,"$");
                $wbwId = explode('-',$word->id->__toString());

                $wbwData = [
                    'uid'=>$wbwrow->uid,
                    'book'=>$wbwrow->book_id,
                    'para'=>$wbwrow->paragraph,
                    'sn'=> array_slice($wbwId,2),
                    'word'=>['value'=>$word->pali->__toString(),'status'=>0],
                    'real'=> ['value'=>$word->real->__toString(),'status'=>0],
                    'meaning'=> ['value'=>$word->mean->__toString() ,'status'=>0],
                    'type'=> ['value'=>$word->type->__toString(),'status'=>0],
                    'grammar'=> ['value'=>$word->gramma->__toString(),'status'=>0],
                    'case'=> ['value'=>$word->case->__toString(),'status'=>0],
                    'parent'=> ['value'=>$word->parent->__toString(),'status'=>0],
                    'style'=> ['value'=>$word->style->__toString(),'status'=>0],
                    'factors'=> ['value'=>$word->org->__toString(),'status'=>0],
                    'factorMeaning'=> ['value'=>$word->om->__toString(),'status'=>0],
                    'confidence'=> $word->cf->__toString(),
                    'created_at'=> $wbwrow->created_at,
                    'updated_at'=> $wbwrow->updated_at,
                    'hasComment'=>Discussion::where('res_id',$wbwrow->uid)->exists(),
                ];
                if(isset($word->parent2)){
                    $wbwData['parent2']['value'] = $word->parent2->__toString();
                    if(isset($word->parent2['status'])){
                        $wbwData['parent2']['status'] = (int)$word->parent2['status'];
                    }else{
                        $wbwData['parent2']['status'] = 0;
                    }
                }
                if(isset($word->pg)){
                    $wbwData['grammar2']['value'] = $word->pg->__toString();
                    if(isset($word->pg['status'])){
                        $wbwData['grammar2']['status'] = (int)$word->pg['status'];
                    }else{
                        $wbwData['grammar2']['status'] = 0;
                    }
                }
                if(isset($word->rela)){
                    $wbwData['relation']['value'] = $word->rela->__toString();
                    if(isset($word->rela['status'])){
                        $wbwData['relation']['status'] = (int)$word->rela['status'];
                    }else{
                        $wbwData['relation']['status'] = 7;
                    }
                }
                if(isset($word->bmt)){
                    $wbwData['bookMarkText']['value'] = $word->bmt->__toString();
                    if(isset($word->bmt['status'])){
                        $wbwData['bookMarkText']['status'] = (int)$word->bmt['status'];
                    }else{
                        $wbwData['bookMarkText']['status'] = 7;
                    }
                }
                if(isset($word->bmc)){
                    $wbwData['bookMarkColor']['value'] = $word->bmc->__toString();
                    if(isset($word->bmc['status'])){
                        $wbwData['bookMarkColor']['status'] = (int)$word->bmc['status'];
                    }else{
                        $wbwData['bookMarkColor']['status'] = 7;
                    }
                }
                if(isset($word->note)){
                    $wbwData['note']['value'] = $word->note->__toString();
                    if(isset($word->note['status'])){
                        $wbwData['note']['status'] = (int)$word->note['status'];
                    }else{
                        $wbwData['note']['status'] = 7;
                    }
                }
                if(isset($word->cf)){
                    $wbwData['confidence'] = (float)$word->cf->__toString();
                }
                if(isset($word->attachments)){
                    $wbwData['attachments'] = json_decode($word->attachments->__toString());
                }
                if(isset($word->pali['status'])){
                    $wbwData['word']['status'] = (int)$word->pali['status'];
                }
                if(isset($word->real['status'])){
                    $wbwData['real']['status'] = (int)$word->real['status'];
                }
                if(isset($word->mean['status'])){
                    $wbwData['meaning']['status'] = (int)$word->mean['status'];
                }
                if(isset($word->type['status'])){
                    $wbwData['type']['status'] = (int)$word->type['status'];
                }
                if(isset($word->gramma['status'])){
                    $wbwData['grammar']['status'] = (int)$word->gramma['status'];
                }
                if(isset($word->case['status'])){
                    $wbwData['case']['status'] = (int)$word->case['status'];
                }
                if(isset($word->parent['status'])){
                    $wbwData['parent']['status'] = (int)$word->parent['status'];
                }
                if(isset($word->org['status'])){
                    $wbwData['factors']['status'] = (int)$word->org['status'];
                }
                if(isset($word->om['status'])){
                    $wbwData['factorMeaning']['status'] = (int)$word->om['status'];
                }

                $wbwContent[] = $wbwData;
            }
        }
        if(count($wbwContent)===0){
            return false;
        }
        return \json_encode($wbwContent,JSON_UNESCAPED_UNICODE);

    }
    /**
     * 将句子放进结果列表
     */
	private function pushSent($result,$sent,$level=0,$mode='read'){

		$sentProps = base64_encode(\json_encode($sent)) ;
        if($mode === 'read'){
            $sentWidget = "<MdTpl tpl='sentread' props='{$sentProps}' ></MdTpl>";
        }else{
            $sentWidget = "<MdTpl tpl='sentedit' props='{$sentProps}' ></MdTpl>";
        }
		//增加标题的html标记
		if($level>0){
			$sentWidget = "<h{$level}>".$sentWidget."</h{$level}>";
		}
		array_push($result,$sentWidget);
        return $result;
	}
	private function newSent($book,$para,$word_start,$word_end){
		$sent = [
            "id"=>"{$book}-{$para}-{$word_start}-{$word_end}",
            "book"=>$book,
            "para"=>$para,
            "wordStart"=>$word_start,
            "wordEnd"=>$word_end,
			"origin"=>[],
			"translation"=>[],
		];

        if($book<1000){
            #生成channel 数量列表
            $sentId = "{$book}-{$para}-{$word_start}-{$word_end}";
            $channelCount = CorpusController::_sentCanReadCount($book,$para,$word_start,$word_end,$this->userUuid);
            $path = json_decode(PaliText::where('book',$book)->where('paragraph',$para)->value("path"),true);
            $sent["path"] = [];
            foreach ($path as $key => $value) {
                # code...
                $value['paliTitle'] = $value['title'];
                $sent["path"][] = $value;
            }
            $sent["tranNum"] = $channelCount['tranNum'];
            $sent["nissayaNum"] = $channelCount['nissayaNum'];
            $sent["commNum"] = $channelCount['commNum'];
            $sent["originNum"] = $channelCount['originNum'];
            $sent["simNum"] = $channelCount['simNum'];
        }

		return $sent;
	}

    public static function _sentCanReadCount($book,$para,$start,$end,$userUuid=null){
        $keyCanRead="/channel/can-read/";
        if($userUuid){
            $keyCanRead .= $userUuid;
        }else{
            $keyCanRead .= 'guest';
        }
        $channelCanRead = RedisClusters::remember($keyCanRead,
                            config('mint.cache.expire'),
                            function() use($userUuid){
                                return ChannelApi::getCanReadByUser($userUuid);
                            });
        $channels =  Sentence::where('book_id',$book)
            ->where('paragraph',$para)
            ->where('word_start',$start)
            ->where('word_end',$end)
            ->where('strlen','<>',0)
            ->whereIn('channel_uid',$channelCanRead)
            ->select('channel_uid')
            ->groupBy('channel_uid')
            ->get();
        $channelList = [];
        foreach ($channels as $key => $value) {
            # code...
            if(Str::isUuid($value->channel_uid)){
                $channelList[] = $value->channel_uid;
            }
        }
        $simId = PaliSentence::where('book',$book)
                            ->where('paragraph',$para)
                            ->where('word_begin',$start)
                            ->where('word_end',$end)
                            ->value('id');
        if($simId){
            $output["simNum"]=SentSimIndex::where('sent_id',$simId)->value('count');
        }else{
            $output["simNum"]=0;
        }
        $channelInfo = Channel::whereIn("uid",$channelList)->select('type')->get();
        $output["tranNum"]=0;
        $output["nissayaNum"]=0;
        $output["commNum"]=0;
        $output["originNum"]=0;

        foreach ($channelInfo as $key => $value) {
            # code...
            switch($value->type){
                case "translation":
                    $output["tranNum"]++;
                    break;
                case "nissaya":
                    $output["nissayaNum"]++;
                    break;
                case "commentary":
                    $output["commNum"]++;
                    break;
                case "original":
                    $output["originNum"]++;
                    break;
            }
        }
        return $output;
    }
    /**
     * 获取某个句子的相关资源的句子数量
     */
    public static function sentCanReadCount($book,$para,$start,$end,$userUuid=null){
		$sentId = "{$book}-{$para}-{$start}-{$end}";
        $hKey = "/sentence/res-count/{$sentId}/";
        if($userUuid){
            $key = $userUuid;
        }else{
            $key = 'guest';
        }
        if(Redis::hExists($hKey,$key)){
            return json_decode(Redis::hGet($hKey,$key),true);
        }else{
            $channelCount = CorpusController::_sentCanReadCount($book,$para,$start,$end,$userUuid);
            Redis::hSet($hKey,$key,json_encode($channelCount));
            return $channelCount;
        }
    }

    private function markdownRender($input){

    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Sentence  $sentence
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Sentence $sentence)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Sentence  $sentence
     * @return \Illuminate\Http\Response
     */
    public function destroy(Sentence $sentence)
    {
        //
    }
}
