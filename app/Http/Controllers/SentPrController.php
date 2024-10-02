<?php


namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

use App\Models\SentPr;
use App\Models\Channel;
use App\Models\PaliSentence;
use App\Models\Sentence;
use App\Models\Notification;
use App\Http\Resources\SentPrResource;
use App\Http\Api\Mq;
use App\Http\Api\AuthApi;

class SentPrController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        switch ($request->get('view')) {
            case 'sent-info':
                $table = SentPr::where('book_id',$request->get('book'))
                                ->where('paragraph',$request->get('para'))
                                ->where('word_start',$request->get('start'))
                                ->where('word_end',$request->get('end'))
                                ->where('channel_uid',$request->get('channel'));
                $all_count = $table->count();
                $result = $table->orderBy('created_at','desc')->get();

                break;
        }
        if($result){
            //修改notification 已读状态
            $user = AuthApi::current($request);
            if($user){
                $id=array();
                foreach ($result as $key => $row) {
                    $id[] = $row->uid;
                }
                Notification::whereIn('res_id',$id)
                            ->where('to',$user['user_uid'])
                            ->update(['status'=>'read']);
            }
            return $this->ok([
                    "rows"=>SentPrResource::collection($result),
                    "count"=>$all_count
                ]);
        }else{
            return $this->error("no data");
        }
    }

    public function pr_tree(Request $request){
        $output = [];
        $sentences = $request->get("data");
        foreach ($sentences as $key => $sentence) {
            # 先查句子信息
            $sentInfo = Sentence::where('book_id',$sentence['book'])
                                ->where('paragraph',$sentence['paragraph'])
                                ->where('word_start',$sentence['word_start'])
                                ->where('word_end',$sentence['word_end'])
                                ->where('channel_uid',$sentence['channel_id'])
                                ->first();
            $sentPr = SentPr::where('book_id',$sentence['book'])
                            ->where('paragraph',$sentence['paragraph'])
                            ->where('word_start',$sentence['word_start'])
                            ->where('word_end',$sentence['word_end'])
                            ->where('channel_uid',$sentence['channel_id'])
                            ->select('content','editor_uid')
                            ->orderBy('created_at','desc')->get();
            if(count($sentPr)>0){
                if($sentInfo){
                    $content = $sentInfo->content;
                }else{
                    $content = "null";
                }
                $output[] = [
                    'sentence' => [
                        'book' => $sentence['book'],
                        'paragraph' => $sentence['paragraph'],
                        'word_start' => $sentence['word_start'],
                        'word_end' => $sentence['word_end'],
                        'channel_id' => $sentence['channel_id'],
                        'content' => $content,
                        'pr_count' => count($sentPr),
                    ],
                    'pr' => $sentPr,
                ];
            }

        }
        return $this->ok(['rows'=>$output,'count'=>count($output)]);
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
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'),401,401);
        }
        $user_uid = $user['user_uid'];

        $data = $request->all();


		#查询是否存在
		#同样的内容只能提交一次
		$exists = SentPr::where('book_id',$data['book'])
						->where('paragraph',$data['para'])
						->where('word_start',$data['begin'])
						->where('word_end',$data['end'])
						->where('content',$data['text'])
						->where('channel_uid',$data['channel'])
						->exists();
        if($exists){
            return $this->error("已经存在同样的修改建议",200,200);
        }

        #不存在，新建
        $new = new SentPr();
        $new->id = app('snowflake')->id();
        $new->uid = Str::uuid();
        $new->book_id = $data['book'];
        $new->paragraph = $data['para'];
        $new->word_start = $data['begin'];
        $new->word_end = $data['end'];
        $new->channel_uid = $data['channel'];
        $new->editor_uid = $user_uid;
        $new->content = $data['text'];
        $new->language = Channel::where('uid',$data['channel'])->value('lang');
        $new->status = 1;//未处理状态
        $new->strlen = mb_strlen($data['text'],"UTF-8");
        $new->create_time = time()*1000;
        $new->modify_time = time()*1000;
        $new->save();
        Mq::publish('suggestion',['data'=>new SentPrResource($new),
                                  'token'=>AuthApi::getToken($request)]);

		$robotMessageOk=true;
		$webHookMessage="";

		#同时返回此句子pr数量
		$info['book_id'] = $data['book'];
		$info['paragraph'] = $data['para'];
		$info['word_start'] = $data['begin'];
		$info['word_end'] = $data['end'];
		$info['channel_uid'] = $data['channel'];
		$count = SentPr::where('book_id' , $data['book'])
						->where('paragraph' , $data['para'])
						->where('word_start' , $data['begin'])
						->where('word_end' , $data['end'])
						->where('channel_uid' , $data['channel'])
						->count();

		return $this->ok(["new"=>$info,"count"=>$count,"webhook"=>["message"=>$webHookMessage,"ok"=>$robotMessageOk]]);

    }

    /**
     * Display the specified resource.
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $uid
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request,string $uid)
    {
        //

        $pr = SentPr::where('uid',$uid)->first();
        if(!$pr){
            return $this->error('no data',404,404);
        }
        //修改notification 已读状态
        $user = AuthApi::current($request);
        if($user){
            Notification::where('res_id',$uid)
                        ->where('to',$user['user_uid'])
                        ->update(['status'=>'read']);
        }

        return $this->ok(new SentPrResource($pr));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SentPr  $sentPr
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, string $id)
    {
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'),401,401);
        }

		$sentPr = SentPr::find($id);
        if(!$sentPr){
            return $this->error('no res');
        }
		if($sentPr->editor_uid !== $user['user_uid']){
            return $this->error('not power',403,403);
        }
        $sentPr->content = $request->get('text');
        $sentPr->modify_time = time()*1000;
        $sentPr->save();
        return $this->ok($sentPr);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, string $id)
    {
        //
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'),401,401);
        }
		$old = SentPr::where('id', $id)->first();
        if(!$old){
            return $this->error('no res');
        }
        //鉴权
        if($old->editor_uid !== $user["user_uid"]){
            return $this->error(__('auth.failed'),403,403);
        }
		$result = SentPr::where('id', $id)
						->where('editor_uid', $user["user_uid"])
						->delete();
		if($result>0){
					#同时返回此句子pr数量
		    $count = SentPr::where('book_id' , $old->book_id)
						->where('paragraph' , $old->paragraph)
						->where('word_start' , $old->word_start)
						->where('word_end' , $old->word_end)
						->where('channel_uid' , $old->channel_uid)
						->count();
			return $this->ok($count);
		}else{
			return $this->error('not power',403,403);
		}
    }
}
