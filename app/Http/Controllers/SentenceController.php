<?php

namespace App\Http\Controllers;

use App\Models\Sentence;
use App\Models\Channel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Resources\SentResource;

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
		$indexCol = ['id','book_id','paragraph','word_start','word_end','content','channel_uid','updated_at'];

		switch ($request->get('view')) {
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
			default:
				# code...
				break;
		}
        if(!empty($request->get('order')) && !empty($request->get('dir'))){
            $table->orderBy($request->get('order'),$request->get('dir'));
        }else{
            $table->orderBy('updated_at','desc');
        }
        $count = $table->count();
        if(!empty($request->get('limit'))){
            $offset = 0;
            if(!empty($request->get("offset"))){
                $offset = $request->get("offset");
            }
            $table->skip($offset)->take($request->get('limit'));
        }
        $result = $table->get();
		if($result){
			return $this->ok(["rows"=>$result,"count"=>$count]);
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
            $query[] = $ids;
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
        $user = \App\Http\Api\AuthApi::current($request);
        if(!$user ){
            //未登录用户
            return $this->error(__('auth.failed'));
        }
        $channel = Channel::where('uid',$request->get('channel'))->first();
        if(!$channel){
            return $this->error(__('auth.failed'));
        }
        if($channel->owner_uid !== $user["user_uid"]){
            //判断是否为协作
            $power = ShareApi::getResPower($user["user_uid"],$channel->uid);
            if($power<30){
                return $this->error(__('auth.failed'));
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
        }
        return $this->ok(count($request->get('sentences')));
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

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Sentence  $sentence
     * @return \Illuminate\Http\Response
     */
    public function edit(Sentence $sentence)
    {
        //
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
        $user = \App\Http\Api\AuthApi::current($request);
        if($user ){
            $channel = Channel::where('uid',$param[4])->first();
            if($channel && $channel->owner_uid === $user["user_uid"]){
                $sent = Sentence::firstOrNew([
                    "book_id"=>$param[0],
                    "paragraph"=>$param[1],
                    "word_start"=>$param[2],
                    "word_end"=>$param[3],
                    "channel_uid"=>$param[4],
                ],[
                    "id"=>app('snowflake')->id(),
                    "uid"=>Str::orderedUuid(),
                ]);
                $sent->content = $request->get('content');
                $sent->language = $channel->lang;
                $sent->status = $channel->status;
                $sent->editor_uid = $user["user_uid"];
                $sent->save();
                return $this->ok(new SentResource($sent));
            }else{
                //TODO 判断是否为协作
                return $this->error(__('auth.failed'));
            }
        }else{
            //非所有者鉴权失败

            return $this->error(__('auth.failed'));
        }
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
