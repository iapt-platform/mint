<?php

namespace App\Http\Controllers;

use App\Models\Sentence;
use App\Models\Channel;
use App\Models\PaliText;
use App\Models\WbwTemplate;
use App\Models\WbwBlock;
use App\Models\Wbw;
use App\Models\Discussion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Http\Api\MdRender;
use App\Http\Api\SuggestionApi;
use App\Http\Api\ChannelApi;
use Illuminate\Support\Facades\Log;

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
        "status"=>30,
        "lang"=> "",
        "created_at"=> "",
        "updated_at"=> "",
    ];
    protected $wbwChannels = [];
    protected $selectCol = ['book_id','paragraph','word_start',"word_end",'channel_uid','content','updated_at'];
    public function __construct()
    {


    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
    public function getSentTpl($id,$channels){
        $sent = [];
        $sentId = \explode('-',$id);
        $channelId = ChannelApi::getSysChannel('_System_Wbw_VRI_');
        if($channelId !== false){
            array_push($channels,$channelId);
        }
        $record = Sentence::select($this->selectCol)
        ->where('book_id',$sentId[0])
        ->where('paragraph',$sentId[1])
        ->where('word_start',(int)$sentId[2])
        ->where('word_end',(int)$sentId[3])
        ->whereIn('channel_uid',$channels)
        ->get();
        Log::info("sent count:".count($record));
        $channelIndex = $this->getChannelIndex($channels);

        $content = $this->makeContent($record,"edit",$channelIndex);
        return $content;
    }
    /**
     * Display the specified resource.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function showSent($id)
    {
        //
        $param = \explode('_',$id);
        if(count($param)>1){
            $channels = array_slice($param,1);
        }else{
            $channels = [];
        }
        $this->result['content'] = getSentTpl($param[0],$channels);
        return $this->ok($this->result);
    }

    public function showSentences($type,$id,$mode='read'){
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
		$channelInfo = Channel::whereIn("uid",$channels)->select(['uid','type','name'])->get();
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

        $this->result['content'] = $this->makeContent($record,$mode,$indexChannel);
        return $this->ok($this->result);
    }
    public function showChapter($id,$mode='read')
    {
        //
        $param = \explode('_',$id);
        $sentId = \explode('-',$param[0]);
        $channels = [];
        if(count($param)>1){
            $channels = array_slice($param,1);
        }
        if($mode === 'read'){
            //阅读模式加载md格式原文
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
        if(empty($chapter->toc)){
            $this->result['title'] = "unknown";
        }else{
            $this->result['title'] = $chapter->toc;
            $this->result['sub_title'] = $chapter->toc;
            $this->result['path'] = json_decode($chapter->path);
        }

        $paraFrom = $sentId[1];
        $paraTo = $sentId[1]+$chapter->chapter_len-1;
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
		$channelInfo = Channel::whereIn("uid",$channels)->select(['uid','type','name'])->get();
		$indexChannel = [];
		foreach ($channelInfo as $key => $value) {
			# code...
			$indexChannel[$value->uid] = $value;
            if($value->type==="translation" ){
                $tranChannels[] = $value->uid;
            }
		}
        //获取wbw channel
        //目前默认的 wbw channel 是第一个translation channel
        foreach ($channels as $key => $value) {
            # code...
            if($indexChannel[$value]->type==='translation'){
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
            $this->result['title'] = MdRender::render($title->content,$title->channel_uid);
        }

		//获取句子数据
        $record = Sentence::select($this->selectCol)
                    ->where('book_id',$sentId[0])
                    ->whereBetween('paragraph',[$paraFrom,$paraTo])
                    ->whereIn('channel_uid',$channels)
                    ->orderBy('paragraph')
                    ->orderBy('word_start')
                    ->get();
        if(count($record) ===0){
            return $this->error("no data");
        }

        $this->result['content'] = $this->makeContent($record,$mode,$indexChannel,$indexedHeading);
        return $this->ok($this->result);
    }

    private function getChannelIndex($channels){
        #获取channel索引表
        $channelInfo = Channel::whereIn("uid",$channels)->select(['uid','type','name'])->get();
        $indexChannel = [];
        foreach ($channelInfo as $key => $value) {
            # code...
            $indexChannel[$value->uid] = $value;
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
    private function makeContent($record,$mode,$indexChannel,$indexedHeading=[]){
        $content = [];
		$lastSent = "0-0";
		$sentCount = 0;
        $sent = [];
        $sent["origin"] = [];
        $sent["translation"] = [];
        foreach ($record as $key => $value) {
            # 遍历结果生成html文件
            $currSentId = $value->book_id.'-'.$value->paragraph.'-'.$value->word_start.'-'.$value->word_end;
			if($currSentId !== $lastSent){
				if($sentCount > 0){
					//保存上一个句子
					//增加标题的html标记
					$level = 0;
					if(isset($indexedHeading["{$value->book_id}-{$value->paragraph}"])){
						$level = $indexedHeading["{$value->book_id}-{$value->paragraph}"];
					}
					$content = $this->pushSent($content,$sent,$level,$mode);
				}
				//新建句子
				$sent = $this->newSent($value->book_id,$value->paragraph,$value->word_start,$value->word_end);

				$lastSent = $currSentId;
			}
			$sentContent=$value->content;
			$channelType = $indexChannel[$value->channel_uid]->type;
			if($indexChannel[$value->channel_uid]->type==="original" && $mode !== 'read'){
                //非阅读模式下。原文使用逐词解析数据。优先加载第一个translation channel 如果没有。加载默认逐词解析。
				$channelType = 'wbw';
                $html = "";

                if(count($this->wbwChannels)>0){
                    //获取逐词解析数据
                    $wbwBlock = WbwBlock::where('channel_uid',$this->wbwChannels[0])
                                        ->where('book_id',$value->book_id)
                                        ->where('paragraph',$value->paragraph)
                                        ->select('uid')
                                        ->first();
                    if($wbwBlock){
                        //找到逐词解析数据
                        $wbwData = Wbw::where('block_uid',$wbwBlock->uid)
                                      ->whereBetween('wid',[$value->word_start,$value->word_end])
                                      ->select(['data','uid'])
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
                                $wbwContent[] = [
                                    'uid'=>$wbwrow->uid,
                                    'word'=>['value'=>$word->pali->__toString(),'status'=>0],
                                    'real'=> ['value'=>$word->real->__toString(),'status'=>0],
                                    'meaning'=> ['value'=>\explode('$',$word->mean->__toString()) ,'status'=>0],
                                    'type'=> ['value'=>$word->type->__toString(),'status'=>0],
                                    'grammar'=> ['value'=>$word->gramma->__toString(),'status'=>0],
                                    'case'=> ['value'=>\explode('$',$case),'status'=>0],
                                    'parent'=> ['value'=>$word->parent->__toString(),'status'=>0],
                                    'style'=> ['value'=>$word->style->__toString(),'status'=>0],
                                    'factors'=> ['value'=>$word->org->__toString(),'status'=>0],
                                    'factorMeaning'=> ['value'=>$word->om->__toString(),'status'=>0],
                                    'confidence'=> 0.5,
                                    'hasComment'=>Discussion::where('res_id',$wbwrow->uid)->exists(),
                                ];
                            }
                        }
                        $sentContent = \json_encode($wbwContent);
                    }
                }
			}else{
                $html = Cache::remember("/sent/{$value->channel_uid}/{$currSentId}",10,
                function() use($value){
                    return MdRender::render($value->content,$value->channel_uid);
                });
            }

            $newSent = [
                "content"=>$sentContent,
                "html"=> $html,
                "book"=> $value->book_id,
                "para"=> $value->paragraph,
                "wordStart"=> $value->word_start,
                "wordEnd"=> $value->word_end,
                "editor"=> [
                    'id'=>$value->editor_uid,
                    'nickName'=>'nickname',
                    'realName'=>'realName',
                    'avatar'=>'',
                ],
                "channel"=> [
                    "name"=>$indexChannel[$value->channel_uid]->name,
                    "type"=>$channelType,
	                "id"=> $value->channel_uid,
                ],
                "updateAt"=> $value->updated_at,
                "suggestionCount" => SuggestionApi::getCountBySent($value->book_id,$value->paragraph,$value->word_start,$value->word_end,$value->channel_uid),
            ];
			switch ($indexChannel[$value->channel_uid]->type) {
				case 'original';
				case 'wbw';
					array_push($sent["origin"],$newSent);
					break;
				default:
					array_push($sent["translation"],$newSent);
					break;
			}

			$sentCount++;
        }
		$content = $this->pushSent($content,$sent,0,$mode);
        return \implode("",$content);
    }
	private function pushSent($result,$sent,$level=0,$mode='read'){

		$sentProps = base64_encode(\json_encode($sent)) ;
        if($mode === 'read'){
            $sentWidget = "<MdTpl tpl='sentread' props='{$sentProps}' />";
        }else{
            $sentWidget = "<MdTpl tpl='sentedit' props='{$sentProps}' />";
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
			"origin"=>[],
			"translation"=>[],
		];

		#生成channel 数量列表
		$sentId = "{$book}-{$para}-{$word_start}-{$word_end}";
		$channelCount = Cache::remember("/sent1/{$sentId}/channels/count",
                          60,
                          function() use($book,$para,$word_start,$word_end){
			$channels =  Sentence::where('book_id',$book)
							->where('paragraph',$para)
							->where('word_start',$word_start)
							->where('word_end',$word_end)
							->select('channel_uid')
                            ->groupBy('channel_uid')
							->get();
            $channelList = [];
            foreach ($channels as $key => $value) {
                # code...
                $channelList[] = $value->channel_uid;
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

		});

		$sent["tranNum"] = $channelCount['tranNum'];
		$sent["nissayaNum"] = $channelCount['nissayaNum'];
		$sent["commNum"] = $channelCount['commNum'];
		$sent["originNum"] = $channelCount['originNum'];
		return $sent;
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
