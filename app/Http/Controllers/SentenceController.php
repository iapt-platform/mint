<?php

namespace App\Http\Controllers;

use App\Models\Sentence;
use App\Models\Channel;
use App\Models\SentHistory;
use App\Models\WbwAnalysis;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

use App\Http\Resources\SentResource;
use App\Http\Api\AuthApi;
use App\Http\Api\ShareApi;
use App\Http\Api\ChannelApi;
use App\Http\Api\PaliTextApi;
use App\Http\Api\Mq;

use App\Tools\RedisClusters;
use App\Tools\OpsLog;


class SentenceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = AuthApi::current($request);
        $result=false;
		$indexCol = ['id','uid','book_id','paragraph',
                    'word_start','word_end','content','content_type',
                    'channel_uid','editor_uid','fork_at','acceptor_uid','pr_edit_at','updated_at'];

		switch ($request->get('view')) {
            case 'public':
                //获取全部公开的译文
                //首先获取某个类型的 channel 列表
                $channels = [];
                $channel_type = $request->get('channel_type','translation');
                if($channel_type === "original"){
                    $pali_channel = ChannelApi::getSysChannel("_System_Pali_VRI_");
                    if($pali_channel !== false){
                        $channels[] = $pali_channel;
                    }
                }else{
                    $channelList = Channel::where('type',$channel_type)
                                              ->where('status',30)
                                              ->select('uid')->get();
                    foreach ($channelList as $channel) {
                        # code...
                        $channels[] = $channel->uid;
                    }
                }
                $table = Sentence::select($indexCol)
                                  ->whereIn('channel_uid',$channels)
                                  ->where('updated_at','>',$request->get('updated_after','1970-1-1'));
                break;
            case 'fulltext':
                if(isset($_COOKIE['user_uid'])){
                    $userUid = $_COOKIE['user_uid'];
                }
                $key = $request->get('key');
                if(empty($key)){
			        return $this->error("没有关键词");
                }
                $table = Sentence::select($indexCol)
								  ->where('content','like', '%'.$key.'%')
                                  ->where('editor_uid',$userUid);

                break;
            case 'channel':
                //句子编号列表在某个channel下的全部内容
                $sent = explode(',',$request->get('sentence')) ;
                $query = [];
                foreach ($sent as $value) {
                    # code...
                    $ids = explode('-',$value);
                    $query[] = $ids;
                }
                $table = Sentence::select($indexCol)
                                ->where('channel_uid', $request->get('channel'))
                                ->whereIns(['book_id','paragraph','word_start','word_end'],$query);
                break;
            case 'sent-can-read':
                /**
                 * 某句的全部译文
                 */
                //获取用户有阅读权限的所有channel
                //全网公开
                $type = $request->get('type','translation');
                $channelTable = Channel::where("type",$type)->select(['uid','name']);
                $channelPub = $channelTable->where('status',30)->get();

                $user = AuthApi::current($request);
                $channelShare=array();
                $channelMy=array();
                if($user){
                    //自己的
                    $channelMy = Channel::where('owner_uid',$user['user_uid'])
                                        ->where('type',$type)
                                        ->get();
                    //协作
                    $channelShare = ShareApi::getResList($user['user_uid'],2);
                }
                $channelCanRead = [];
                foreach ($channelPub as $key => $value) {
                    $channelCanRead[$value->uid] = [
                        'id' => $value->uid,
                        'role' => 'member',
                        'name' => $value->name,
                    ];
                }
                foreach ($channelShare as $key => $value) {
                    if($value['type'] === $type){
                        $channelCanRead[$value['res_id']] = [
                            'id' => $value['res_id'],
                            'role' => 'member',
                            'name' => $value['res_title'],
                        ];
                        if($value['power']>=20){
                            $channelCanRead[$value['res_id']]['role'] = "editor";
                        }
                    }
                }
                foreach ($channelMy as $key => $value) {
                    $channelCanRead[$value->uid] = [
                        'id' => $value->uid,
                        'role' => 'owner',
                        'name' => $value->name,
                    ];
                }
                $channels = [];
                $excludeChannels = explode(',',$request->get('excludes')) ;

                foreach ($channelCanRead as $key => $value) {
                    # code...
                    if(!in_array($key,$excludeChannels)){
                        $channels[] = $key;
                    }
                }
                $sent = explode('-',$request->get('sentence')) ;
                $table = Sentence::select($indexCol)
                                ->whereIn('channel_uid', $channels)
                                ->where('ver','>',1)
                                ->where('book_id',$sent[0])
                                ->where('paragraph',$sent[1])
                                ->where('word_start',$sent[2])
                                ->where('word_end',$sent[3]);
                break;
            case 'chapter':
                $chapter =  PaliTextApi::getChapterStartEnd($request->get('book'),$request->get('para'));
                $table = Sentence::where('ver','>',1)
                                    ->where('book_id',$request->get('book'))
                                    ->whereBetween('paragraph',$chapter)
                                    ->whereIn('channel_uid',explode(',',$request->get('channels')));
                break;
            case 'paragraph':
                $table = Sentence::where('ver','>',1)
                                    ->where('book_id',$request->get('book'))
                                    ->whereIn('paragraph',explode(',',$request->get('para')))
                                    ->whereIn('channel_uid',explode(',',$request->get('channels')))
                                    ->orderBy('book_id')->orderBy('paragraph')->orderBy('word_start');
                break;
            case 'my-edit':
                //我编辑的
                if(!$user){
                    return $this->error(__('auth.failed'),401,401);
                }
                $table = Sentence::where('editor_uid',$user['user_uid'])
                                ->where('ver','>',1);
                break;
			default:
				# code...
				break;
		}
        if(!empty($request->get("key"))){
            $table = $table->where('content','like', '%'.$request->get("key").'%');
        }

        $count = $table->count();
        if($request->get('strlen',false)){
            $totalStrLen = $table->sum('strlen');
        }
        if($request->get('view') !== 'paragraph'){
            $table = $table->orderBy($request->get('order','updated_at'),
                                    $request->get('dir','desc'));
        }

        $table = $table->skip($request->get("offset",0))
                       ->take($request->get('limit',1000));
        $result = $table->get();

		if($result){
            $output = ["count"=>$count];
            if($request->get('view') === 'sent-can-read' ||
                $request->get('view') === 'channel' ||
                $request->get('view') === 'chapter' ||
                $request->get('view') === 'paragraph' ||
                $request->get('view') === 'my-edit'
                ){
                $output["rows"] = SentResource::collection($result);
            }else{
                $output["rows"] = $result;
            }
            if(isset($totalStrLen)){
                $output['total_strlen'] = $totalStrLen;
            }
            return $this->ok($output);

		}else{
			return $this->error("没有查询到数据");
		}
    }
    /**
     * 用channel 和句子编号列表查询句子
     */
    public function sent_in_channel(Request $request){
        $sent = $request->get('sentences') ;
        $query = [];
        foreach ($sent as $value) {
            # code...
            $ids = explode('-',$value);
            if(count($ids)===4){
                $query[] = $ids;
            }
        }
        $table = Sentence::select(['id','book_id','paragraph','word_start','word_end','content','channel_uid','updated_at'])
                        ->where('channel_uid', $request->get('channel'))
                        ->whereIns(['book_id','paragraph','word_start','word_end'],$query);
        $result = $table->get();
        if($result){
            return $this->ok(["rows"=>$result,"count"=>count($result)]);
        }else{
            return $this->error("没有查询到数据");
        }
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * 新建多个句子
     * 如果句子存在，修改
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //鉴权
        $user = AuthApi::current($request);
        if(!$user ){
            //未登录用户
            return $this->error(__('auth.failed'),401,401);
        }
        $channel = Channel::where('uid',$request->get('channel'))->first();
        if(!$channel){
            return $this->error(__('auth.failed'),403,403);
        }
        if($channel->owner_uid !== $user["user_uid"]){
            //判断是否为协作
            $power = ShareApi::getResPower($user["user_uid"],$channel->uid,2);
            if($power < 20){
                return $this->error(__('auth.failed'),403,403);
            }
        }
        $sentFirst=null;
        $changedSent = [];
        foreach ($request->get('sentences') as $key => $sent) {
            # code...
            if($sentFirst === null){
                $sentFirst = $sent;
            }
            $row = Sentence::firstOrNew([
                "book_id"=>$sent['book_id'],
                "paragraph"=>$sent['paragraph'],
                "word_start"=>$sent['word_start'],
                "word_end"=>$sent['word_end'],
                "channel_uid"=>$channel->uid,
            ],[
                "id"=>app('snowflake')->id(),
                "uid"=>Str::uuid(),
            ]);
            $row->content = $sent['content'];
            if(isset($sent['content_type']) && !empty($sent['content_type'])){
                $row->content_type = $sent['content_type'];
            }
            $row->strlen = mb_strlen($sent['content'],"UTF-8");
            $row->language = $channel->lang;
            $row->status = $channel->status;
            if($request->has('copy')){
                //复制句子，保留原作者信息
                $row->editor_uid = $sent["editor_uid"];
                $row->acceptor_uid = $user["user_uid"];
                $row->pr_edit_at = $sent["updated_at"];
                if($request->has('fork_from')){
                    $row->fork_at = now();
                }
            }else{
                $row->editor_uid = $user["user_uid"];
                $row->acceptor_uid = null;
                $row->pr_edit_at = null;
            }
            $row->create_time = time()*1000;
            $row->modify_time = time()*1000;
            $row->save();

            $changedSent[] = $row->uid;

            //保存历史记录
            if($request->has('copy')){
                $fork_from = $request->get('fork_from',null);
                $this->saveHistory($row->uid,
                                $sent["editor_uid"],
                                $sent['content'],
                                $user["user_uid"],
                                $fork_from);
            }else{
                $this->saveHistory($row->uid,$user["user_uid"],$sent['content'],$user["user_uid"]);
            }
            //清除缓存
            $sentId = "{$sent['book_id']}-{$sent['paragraph']}-{$sent['word_start']}-{$sent['word_end']}";
            $hKey = "/sentence/res-count/{$sentId}/";
            Redis::del($hKey);
        }
        if($sentFirst !== null){
            Mq::publish('progress',['book'=>$sentFirst['book_id'],
                                'para'=>$sentFirst['paragraph'],
                                'channel'=>$channel->uid,
                                ]);
        }

        $result = Sentence::whereIn('uid', $changedSent)->get();
        return $this->ok([
            'rows'=>SentResource::collection($result),
            'count'=>count($result)
        ]);
    }

    private function saveHistory($uid,$editor,$content,$user_uid=null,$fork_from=null,$pr_from=null){
        $newHis = new SentHistory();
        $newHis->id = app('snowflake')->id();
        $newHis->sent_uid = $uid;
        $newHis->user_uid = $editor;
        if(empty($content)){
            $newHis->content = "";
        }else{
            $newHis->content = $content;
        }
        if($fork_from){
            $newHis->fork_from = $fork_from;
            $newHis->accepter_uid = $user_uid;
        }
        if($pr_from){
            $newHis->pr_from = $pr_from;
            $newHis->accepter_uid = $user_uid;
        }
        $newHis->create_time = time()*1000;
        $newHis->save();
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
        return $this->ok(new SentResource($sentence));
    }


    /**
     * 修改单个句子
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id book_para_start_end_channel
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,  $id)
    {
        //
        $param = \explode('_',$id);

        //鉴权
        $user = AuthApi::current($request);
        if(!$user){
            //未登录鉴权失败
            return $this->error(__('auth.failed'),403,403);
        }
        $channel = Channel::where('uid',$param[4])->first();
        if(!$channel){
            return $this->error("not found channel");
        }
        if($channel->owner_uid !== $user["user_uid"]){
            // 判断是否为协作
            $power = ShareApi::getResPower($user["user_uid"],$channel->uid,2);
            if($power < 20){
                return $this->error(__('auth.failed'),403,403);
            }
        }

        $sent = Sentence::firstOrNew([
            "book_id"=>$param[0],
            "paragraph"=>$param[1],
            "word_start"=>$param[2],
            "word_end"=>$param[3],
            "channel_uid"=>$param[4],
        ],[
            "id"=>app('snowflake')->id(),
            "uid"=>Str::orderedUuid(),
            "create_time"=>time()*1000,
        ]);
        $sent->content = $request->get('content');
        if($request->has('contentType')){
            $sent->content_type = $request->get('contentType');
        }
        $sent->language = $channel->lang;
        $sent->status = $channel->status;
        $sent->strlen = mb_strlen($request->get('content'),"UTF-8");
        $sent->modify_time = time()*1000;
        if($request->has('prEditor')){
            $realEditor = $request->get('prEditor');
            $sent->acceptor_uid = $user["user_uid"];
            $sent->pr_edit_at = $request->get('prEditAt');
            $sent->pr_id = $request->get('prId');
        }else{
            $realEditor = $user["user_uid"];
            $sent->acceptor_uid = null;
            $sent->pr_edit_at = null;
            $sent->pr_id = null;
        }
        $sent->editor_uid = $realEditor;
        $sent->save();
        $sent = $sent->refresh();
        //清除缓存
        $sentId = "{$sent['book_id']}-{$sent['paragraph']}-{$sent['word_start']}-{$sent['word_end']}";
        $hKey = "/sentence/res-count/{$sentId}/";
        Redis::del($hKey);
        OpsLog::debug($user["user_uid"],$sent);

        //清除cache
        $channelId = $param[4];
        $currSentId = "{$param[0]}-{$param[1]}-{$param[2]}-{$param[3]}";
        RedisClusters::forget("/sent/{$channelId}/{$currSentId}");
        //保存历史记录
        if($request->has('prEditor')){
            $this->saveHistory($sent->uid,
                            $realEditor,
                            $request->get('content'),
                            $user["user_uid"],
                            null,
                            $request->get('prUuid'),
                        );
        }else{
            $this->saveHistory($sent->uid,$realEditor,$request->get('content'));
        }

        Mq::publish('progress',['book'=>$param[0],
                            'para'=>$param[1],
                            'channel'=>$channelId,
                            ]);
        Mq::publish('content',new SentResource($sent));

        if($channel->type === 'nissaya' && $sent->content_type === 'json'){
            $this->updateWbwAnalyses($sent->content,$channel->lang,$user["user_id"]);
        }

        return $this->ok(new SentResource($sent));
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

    private function updateWbwAnalyses($data,$lang,$editorId){
        $wbwData = json_decode($data);
        $currWbwId = 0;
        $prefix = 'wbw-preference';
        foreach ($wbwData as $key => $word) {
            # code...
            if(count($word->sn) === 1 ){
                $currWbwId = $word->uid;
                WbwAnalysis::where('wbw_id',$word->uid)->delete();
            }
            $newData = [
                'wbw_id' => $currWbwId,
                'wbw_word' => $word->real->value,
                'book_id' => $word->book,
                'paragraph' => $word->para,
                'wid' => $word->sn[0],
                'type' => 0,
                'data' => '',
                'confidence' => 100,
                'lang' => $lang,
                'editor_id'=>$editorId,
                'created_at'=>now(),
                'updated_at'=>now()
            ];
            $newData['type'] = 3;
            if(!empty($word->meaning->value)){
                $newData['data'] = $word->meaning->value;
                WbwAnalysis::insert($newData);
                RedisClusters::put("{$prefix}/{$word->real->value}/3/{$editorId}",$word->meaning->value);
                RedisClusters::put("{$prefix}/{$word->real->value}/3/0",$word->meaning->value);
            }
            if(isset($word->factors) && isset($word->factorMeaning)){
                $factors = explode('+',str_replace('-','+',$word->factors->value));
                $factorMeaning = explode('+',str_replace('-','+',$word->factorMeaning->value));
                foreach ($factors as $key => $factor) {
                    if(isset($factorMeaning[$key])){
                        if(!empty($factorMeaning[$key])){
                            $newData['wbw_word'] = $factor;
                            $newData['data'] = $factorMeaning[$key];
                            $newData['type'] = 5;
                            WbwAnalysis::insert($newData);
                            RedisClusters::put("{$prefix}/{$factor}/5/{$editorId}",$factorMeaning[$key]);
                            RedisClusters::put("{$prefix}/{$factor}/5/0",$factorMeaning[$key]);
                        }
                    }
                }
            }
        }
    }
}
