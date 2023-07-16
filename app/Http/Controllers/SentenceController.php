<?php

namespace App\Http\Controllers;

use App\Models\Sentence;
use App\Models\Channel;
use App\Models\SentHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Resources\SentResource;
use App\Http\Api\AuthApi;
use App\Http\Api\ShareApi;
use App\Http\Api\ChannelApi;
use Illuminate\Support\Facades\Log;

class SentenceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $result=false;
		$indexCol = ['id','uid','book_id','paragraph','word_start','word_end','content','content_type','channel_uid','editor_uid','acceptor_uid','pr_edit_at','updated_at'];

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
                foreach ($channelCanRead as $key => $value) {
                    # code...
                    $channels[] = $key;
                }
                $sent = explode('-',$request->get('sentence')) ;
                $table = Sentence::select($indexCol)
                                ->whereIn('channel_uid', $channels)
                                ->where('book_id',$sent[0])
                                ->where('paragraph',$sent[1])
                                ->where('word_start',$sent[2])
                                ->where('word_end',$sent[3]);
			default:
				# code...
				break;
		}
        $count = $table->count();
        if($request->get('strlen',false)){
            $totalStrLen = $table->sum('strlen');
        }
        $table = $table->orderBy($request->get('order','updated_at'),$request->get('dir','desc'));
        $table = $table->skip($request->get("offset",0))
                       ->take($request->get('limit',1000));
        $result = $table->get();

		if($result){
            if($request->get('view') === 'sent-can-read'){
                $output = ["rows"=>SentResource::collection($result),"count"=>$count];
            }else{
                $output = ["rows"=>$result,"count"=>$count];
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
            return $this->error(__('auth.failed'),[],401);
        }
        $channel = Channel::where('uid',$request->get('channel'))->first();
        if(!$channel){
            return $this->error(__('auth.failed'));
        }
        if($channel->owner_uid !== $user["user_uid"]){
            //判断是否为协作
            $power = ShareApi::getResPower($user["user_uid"],$channel->uid,2);
            if($power < 20){
                return $this->error(__('auth.failed'),[],403);
            }
        }
        foreach ($request->get('sentences') as $key => $sent) {
            # code...
            $row = Sentence::firstOrNew([
                "book_id"=>$sent['book_id'],
                "paragraph"=>$sent['paragraph'],
                "word_start"=>$sent['word_start'],
                "word_end"=>$sent['word_end'],
                "channel_uid"=>$channel->uid,
            ],[
                "id"=>app('snowflake')->id(),
                "uid"=>Str::orderedUuid(),
            ]);
            $row->content = $sent['content'];
            $row->strlen = mb_strlen($sent['content'],"UTF-8");
            $row->language = $channel->lang;
            $row->status = $channel->status;
            $row->editor_uid = $user["user_uid"];
            $row->create_time = time()*1000;
            $row->modify_time = time()*1000;
            $row->save();

            //保存历史记录
            $this->saveHistory($row->uid,$user["user_uid"],$sent['content']);
        }
        return $this->ok(count($request->get('sentences')));
    }

    private function saveHistory($uid,$editor,$content){
        $newHis = new SentHistory();
        $newHis->id = app('snowflake')->id();
        $newHis->sent_uid = $uid;
        $newHis->user_uid = $editor;
        $newHis->content = $content;
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
            return $this->error(__('auth.failed'),[],403);
        }
        $channel = Channel::where('uid',$param[4])->first();
        if(!$channel){
            return $this->error("not found channel");
        }
        if($channel->owner_uid !== $user["user_uid"]){
            //TODO 判断是否为协作
            $power = ShareApi::getResPower($user["user_uid"],$channel->uid,2);
            if($power < 20){
                return $this->error(__('auth.failed'),[],403);
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
        $sent->editor_uid = $user["user_uid"];
        $sent->strlen = mb_strlen($request->get('content'),"UTF-8");
        $sent->modify_time = time()*1000;
        if($request->has('prEditor')){
            $sent->acceptor_uid = $user["user_uid"];
            $sent->pr_edit_at = $request->get('prEditAt');
            $sent->editor_uid = $request->get('prEditor');
            $sent->pr_id = $request->get('prId');
        }
        $sent->save();
        //保存历史记录
        $this->saveHistory($sent->uid,$user["user_uid"],$request->get('content'));
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
}
