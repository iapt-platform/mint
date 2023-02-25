<?php

namespace App\Http\Controllers;

use App\Models\DhammaTerm;
use App\Models\Channel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Http\Api\AuthApi;
use App\Http\Api\StudioApi;

class DhammaTermController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $result=false;
		$indexCol = ['id','guid','word','meaning','other_meaning','note','language','channal','created_at','updated_at'];

		switch ($request->get('view')) {
            case 'create-by-channel':
                # 新建术语时。根据术语所在channel 给出新建术语所需数据。如语言，备选意思等。
                #获取channel信息
                $currChannel = Channel::where('uid',$request->get('channel'))->first();
                if(!$currChannel){
                    return $this->error(__('auth.failed'));
                }
                #TODO 查询studio信息
                #获取同studio的channel列表
                $studioChannels = Channel::where('owner_uid',$currChannel->owner_uid)
                                        ->select(['name','uid'])
                                        ->get();
                #获取全网意思列表
                $meanings = DhammaTerm::where('word',$request->get('word'))
                                        ->where('language',$currChannel->lang)
                                        ->select(['meaning','other_meaning'])
                                        ->get();
                $meaningList=[];
                foreach ($meanings as $key => $value) {
                    # code...
                    $meaning1 = [$value->meaning];

                    if(!empty($value->other_meaning)){
                        $meaning2 = \explode(',',$value->other_meaning);
                        $meaning1 = array_merge($meaning1,$meaning2);
                    }
                    foreach ($meaning1 as $key => $value) {
                        # code...
                        if(isset($meaningList[$value])){
                            $meaningList[$value]++;
                        }else{
                            $meaningList[$value] = 1;
                        }
                    }
                }
                $meaningCount = [];
                foreach ($meaningList as $key => $value) {
                    # code...
                    $meaningCount[] = ['meaning'=>$key,'count'=>$value];
                }
                return $this->ok([
                    "word"=>$request->get('word'),
                    "meaningCount"=>$meaningCount,
                    "studioChannels"=>$studioChannels,
                    "language"=>$currChannel->lang,
                ]);
                break;
            case 'studio':
				# 获取studio内所有channel
                $search = $request->get('search');
                $user = AuthApi::current($request);
                if($user){
                    //判断当前用户是否有指定的studio的权限
                    if($user['user_uid'] === StudioApi::getIdByName($request->get('name'))){
                        $table = DhammaTerm::select($indexCol)
                                    ->where('owner', $user["user_uid"]);
                    }else{
                        return $this->error(__('auth.failed'));
                    }
                }else{
                    return $this->error(__('auth.failed'));
                }
				break;
            case 'show':
                return $this->ok(DhammaTerm::find($request->get('id')));
                break;
			case 'user':
				# code...
                $userUid = $_COOKIE['user_uid'];
                $search = $request->get('search');
				$table = DhammaTerm::select($indexCol)
									->where('owner', $userUid);
				break;
			case 'word':
				$table = DhammaTerm::select($indexCol)
									->where('word', $request->get("word"));
				break;
            case 'hot-meaning':
                $key='term/hot_meaning';
                $value = Cache::get($key, function()use($request) {
                    $hotMeaning=[];
                    $words = DhammaTerm::select('word')
                                ->where('language',$request->get("language"))
                                ->groupby('word')
                                ->get();

                    foreach ($words as $key => $word) {
                        # code...
                        $result = DhammaTerm::select(DB::raw('count(*) as word_count, meaning'))
                                ->where('language',$request->get("language"))
                                ->where('word',$word['word'])
                                ->groupby('meaning')
                                ->orderby('word_count','desc')
                                ->first();
                        if($result){
                            $hotMeaning[]=[
                                'word'=>$word['word'],
                                'meaning'=>$result['meaning'],
                                'language'=>$request->get("language"),
                                'owner'=>'',
                            ];
                        }
                    }
                    Cache::put($key, $hotMeaning, 3600);
                    return $hotMeaning;
                });
                return $this->ok(["rows"=>$value,"count"=>count($value)]);
                break;
			default:
				# code...
				break;
		}
        if(!empty($search)){
            $table->where('word', 'like', $search."%")
                  ->orWhere('word_en', 'like', $search."%")
                  ->orWhere('meaning', 'like', "%".$search."%");
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
                // validate
        // read more on validation at http://laravel.com/docs/validation
        $rules = array(
            'word' => 'required',
            'meaning' => 'required',
            'language' => 'required'
        );
        $validator = Validator::make($request->all(), $rules);

        // process the login
        if ($validator->fails()) {
            return $this->error($validator);
        }

        #查询重复的
        /*
        重复判定：
        一个channel下面word+tag+language 唯一
        */
        $table = DhammaTerm::where('owner', $_COOKIE["user_uid"])
                ->where('word',$request->get("word"))
                ->where('tag',$request->get("tag"));
        if($request->get("channel")){
            $isDoesntExist = $table->where('channel',$request->get("channel"))
                    ->doesntExist();
        }else{
            $isDoesntExist = $table->where('language',$request->get("language"))
                ->doesntExist();
        }

        if($isDoesntExist){
            #不存在插入数据
            $term = new DhammaTerm;
            $term->id=app('snowflake')->id();
            $term->guid=Str::uuid();
            $term->word=$request->get("word");
            $term->meaning=$request->get("meaning");
            $term->save();
            return $this->ok($data);

        }else{
            return $this->error("word existed");
        }
        // store
        /*
        $data = $request->all();
        $data['id'] = app('snowflake')->id();
        $data['guid'] = Str::uuid();
        DhammaTerm::create($data);
        */


    }

    /**
     * Display the specified resource.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request  $request,$id)
    {
        //
		$indexCol = ['id','guid','word','meaning','other_meaning','note','language','channal','created_at','updated_at'];

		$result  = DhammaTerm::select($indexCol)->where('guid', $id)->first();
		if($result){
			return $this->ok($result);
		}else{
			return $this->error("没有查询到数据");
		}

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DhammaTerm  $dhammaTerm
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DhammaTerm $dhammaTerm)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DhammaTerm  $dhammaTerm
     * @return \Illuminate\Http\Response
     */
    public function destroy(DhammaTerm $dhammaTerm,Request $request)
    {
        /**
         * 一次删除多个单词
         */
        if(isset($_COOKIE["user_uid"])){
            $user_uid = $_COOKIE["user_uid"];
        }else{
            $user = AuthApi::current($request);
            if(!$user){
                return $this->error(__('auth.failed'));
            }
            $user_uid = $user['user_uid'];
        }

        if($request->has("uuid")){
            $count = DhammaTerm::whereIn('guid', $request->get("id"))
                            ->where('owner', $user_uid)
                            ->delete();
        }else{
            $arrId = json_decode($request->get("id"),true) ;
            $count = 0;

            foreach ($arrId as $key => $id) {
                # code...
                $result = DhammaTerm::where('id', $id)
                                ->where('owner', $user_uid)
                                ->delete();
                if($result){
                    $count++;
                }
            }
        }

		return $this->ok($count);
    }
}
