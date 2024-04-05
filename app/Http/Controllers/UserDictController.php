<?php

namespace App\Http\Controllers;

use App\Models\UserDict;
use App\Models\DictInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use App\Http\Api;
use App\Http\Api\AuthApi;
use App\Http\Api\DictApi;
use App\Http\Resources\UserDictResource;
use Illuminate\Support\Str;

class UserDictController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
		$result=false;
		$indexCol = ['id','word','type','grammar',
                     'mean','parent','note','status',
                     'factors','confidence','dict_id',
                     'source','updated_at','creator_id'];
		switch ($request->get('view')) {
            case 'all':
            # 获取studio内所有channel
                $user = AuthApi::current($request);
                if(!$user){
                    return $this->error(__('auth.failed'));
                }
                $table = UserDict::select($indexCol);
                break;
            case 'studio':
				# 获取studio内所有channel
                $user = AuthApi::current($request);
                if(!$user){
                    return $this->error(__('auth.failed'));
                }
                //判断当前用户是否有指定的studio的权限
                if($user['user_uid'] !== \App\Http\Api\StudioApi::getIdByName($request->get('name'))){
                    return $this->error(__('auth.failed'));
                }
                $table = UserDict::select($indexCol)
                            ->where('creator_id', $user["user_id"])
                            ->whereIn('source', ["_USER_WBW_","_USER_DICT_"]);
				break;
			case 'user':
				# code...
				$table = UserDict::select($indexCol)
									->where('creator_id', $_COOKIE["user_id"])
									->where('source', '<>', "_SYS_USER_WBW_");
				break;
			case 'word':
				$table = UserDict::select($indexCol)
								 ->where('word', $request->get("word"));
				break;
            case 'community':
                $table = UserDict::select($indexCol)
                                ->where('word', $request->get("word"))
                                ->where('status', '>',5)
                                ->where(function($query) {
                                    $query->where('source', "_USER_WBW_")
                                            ->orWhere('source','_USER_DICT_');
                                });
                break;
            case 'compound':
                $dict_id = DictApi::getSysDict('robot_compound');
                if($dict_id===false){
                    $this->error('no robot_compound');
                }
                $table = UserDict::where("dict_id",$dict_id)->where("word",$request->get('word'));
                break;
            case 'dict':
                $dict_id = false;
                if($request->has('name')){
                   $dict_id = DictApi::getSysDict($request->get('name'));
                }else if($request->has('id')){
                    $dict_id = $request->get('id');
                }

                if($dict_id===false){
                    $this->error('no dict',[],404);
                }
                $table = UserDict::select($indexCol)
                                 ->where("dict_id",$dict_id);
			default:
				# code...
				break;
		}
        if($request->has("search")){
            $table->where('word', 'like', $request->get("search")."%");
        }
        if(($request->has('word'))){
            $table = $table->where('word',$request->get('word'));
        }
        if(($request->has('parent'))){
            $table = $table->where('parent',$request->get('parent'));
        }
        if(($request->has('dict'))){
            $dictId = DictInfo::where('shortname',$request->get('dict'))->value('id');
            if(Str::isUuid($dictId)){
                $table = $table->where('dict_id',$dictId);
            }
        }
        $count = $table->count();

        $table->orderBy($request->get('order','updated_at'),
                        $request->get('dir','desc'));

        $table->skip($request->get('offset',0))
              ->take($request->get('limit',200));

        $result = $table->get();
        return $this->ok(["rows"=>UserDictResource::collection($result),"count"=>$count]);
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
        $user  = AuthApi::current($request);
		if(!$user){
			$this->error("not login");
		}

		$_data = json_decode($request->get("data"),true);

		switch($request->get('view')){
            case "dict":
                $src = "_USER_DICT_";
                break;
			case "wbw":
                $src = "_USER_WBW_";
                break;
            default:
                $this->error("not view");
                break;
        }
        #查询用户重复的数据
        $iOk = 0;
        $updateOk=0;
        foreach ($_data as $key => $word) {
            # code...
            $table = UserDict::where('creator_id', $user["user_id"])
                                ->where('word',$word["word"]);
            if(isset($word["type"])){$table = $table->where('type',$word["type"]);}
            if(isset($word["grammar"])){$table = $table->where('grammar',$word["grammar"]);}
            if(isset($word["parent"])){$table = $table->where('parent',$word["parent"]);}
            if(isset($word["mean"])){$table = $table->where('mean',$word["mean"]);}
            if(isset($word["factors"])){$table = $table->where('factors',$word["factors"]);}
            $isDoesntExist = $table->doesntExist();
            if($isDoesntExist){
                #不存在插入数据
                $word["id"]=app('snowflake')->id();
                $word["source"] = $src;
                $word["create_time"] = time()*1000;
                $word["creator_id"]=$user["user_id"];
                $id = UserDict::insert($word);
                if(isset($word["status"]) && $word["status"] > 5){
                    $updateOk = $this->update_sys_wbw($word);
                }else{
                    $updateOk = true;
                }
                $this->update_redis($word);
                $iOk++;
            }else{
                //存在，修改数据
                $origin = $table->first();
                if(isset($word["note"])){
                    $origin->note = $word["note"];
                }
                if(isset($word["confidence"])){
                    $origin->confidence = $word["confidence"];
                }
                $origin->save();
            }
        }

        return $this->ok([$iOk,$updateOk]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
		$result = UserDict::find($id);
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
		$newData = $request->all();
		$result = UserDict::where('id', $id)
				->update($newData);
		if($result){
			$updateOk = $this->update_sys_wbw($newData);
			$this->update_redis($newData);
			return $this->ok([$result,$updateOk]);
		}else{
		    return $this->error("没有查询到数据");
		}
    }

    /**
     * Remove the specified resource from storage.
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,$id)
    {
        //
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'),[],403);
        }
        $user_id = $user['user_id'];

        if($request->has("id")){
            $arrId = json_decode($request->get("id"),true) ;
            $count = 0;
            $updateOk = false;
            foreach ($arrId as $key => $id) {
                # 找到对应数据
                $data = UserDict::find($id);
                //查看是否有权限删除
                if($data->creator_id == $user_id){
                    $result = UserDict::where('id', $id)
                                    ->delete();
                    $count += $result;
                    $updateOk = $this->update_sys_wbw($data);
                    $this->update_redis($data);
                }
            }
            return $this->ok([$count,$updateOk]);
        }else{
            //删除单个单词
            $userDict = UserDict::find($id);
            //判断当前用户是否有指定的studio的权限
            if((int)$user_id !== $userDict->creator_id){
                return $this->error(__('auth.failed'));
            }
            $delete = $userDict->delete();
            return $this->ok($delete);
        }

    }
	public function delete(Request $request){
		$arrId = json_decode($request->get("id"),true) ;
		$count = 0;
		$updateOk = false;
		foreach ($arrId as $key => $id) {
			$data = UserDict::where('id',$id)->first();
			if($data){
				# 找到对应数据
				$param = [
					"id"=>$id,
					'creator_id'=>$_COOKIE["user_id"]
				];
				$del = UserDict::where($param)->delete();
				$count += $del;
				$updateOk = $this->update_sys_wbw($data);
				$this->update_redis($data);
			}
		}
		return $this->ok(['deleted'=>$count]);
	}

	/*
	更新系统wbw汇总表
	*/
	private function update_sys_wbw($data){

		#查询用户重复的数据
        if(!isset($data["type"])){$data["type"]=null;}
        if(!isset($data["grammar"])){$data["grammar"]=null;}
        if(!isset($data["parent"])){$data["parent"]=null;}
        if(!isset($data["mean"])){$data["mean"]=null;}
        if(!isset($data["factors"])){$data["factors"]=null;}
        if(!isset($data["factormean"])){$data["factormean"]=null;}

		$count = UserDict::where('word',$data["word"])
                        ->where('type',$data["type"])
                        ->where('grammar',$data["grammar"])
                        ->where('parent',$data["parent"])
                        ->where('mean',$data["mean"])
                        ->where('factors',$data["factors"])
                        ->where('factormean',$data["factormean"])
                        ->where('source',$data["source"])
                        ->count();

		if($count === 0){
            # 没有任何用户有这个数据
			#删除数据
			$result = UserDict::where('word',$data["word"])
			->where('type',$data["type"])
			->where('grammar',$data["grammar"])
			->where('parent',$data["parent"])
			->where('mean',$data["mean"])
			->where('factors',$data["factors"])
			->where('factormean',$data["factormean"])
			->where('source','_SYS_USER_WBW_')
			->delete();
			return($result);

		}else{
            #更新或新增
			#查询最早上传这个数据的用户
			$creator_id = UserDict::where('word',$data["word"])
							->where('type',$data["type"])
							->where('grammar',$data["grammar"])
							->where('parent',$data["parent"])
							->where('mean',$data["mean"])
							->where('factors',$data["factors"])
							->where('factormean',$data["factormean"])
							->whereIn('source',['_USER_WBW_','_USER_DICT_'])
							->orderby("created_at",'asc')
							->value("creator_id");

            $count = UserDict::where('word',$data["word"])
                        ->where('type',$data["type"])
                        ->where('grammar',$data["grammar"])
                        ->where('parent',$data["parent"])
                        ->where('mean',$data["mean"])
                        ->where('factors',$data["factors"])
                        ->where('factormean',$data["factormean"])
                        ->where('source','_SYS_USER_WBW_')
                        ->count();
            if($count === 0){
                #社区字典没有 新增
                $result = UserDict::insert(
				[
                    'id' =>app('snowflake')->id(),
					'word'=>$data["word"],
					'type'=>$data["type"],
					'grammar'=>$data["grammar"],
					'parent'=>$data["parent"],
					'mean'=>$data["mean"],
					'factors'=>$data["factors"],
					'factormean'=>$data["factormean"],
					'language'=>$data["language"],
					'source'=>"_SYS_USER_WBW_",
                    'creator_id' => $data["creator_id"],
					'ref_counter' => 1,
                    'dict_id' => DictApi::getSysDict('community_extract'),
                    "create_time"=>time()*1000
                    ]);
            }else{
                #有，更新
                $result = UserDict::where('word',$data["word"])
                        ->where('type',$data["type"])
                        ->where('grammar',$data["grammar"])
                        ->where('parent',$data["parent"])
                        ->where('mean',$data["mean"])
                        ->where('factors',$data["factors"])
                        ->where('factormean',$data["factormean"])
                        ->where('source','_SYS_USER_WBW_')
                        ->update(
                    [
                        'creator_id'=>$creator_id,
                        'ref_counter'=>$count
                    ]);
            }
			return($result);
		}
	}

	private function update_redis($word){
		#更新 redis
		$Fetch = UserDict::where(['word'=>$word['word'],"source"=>"_USER_WBW_"])->get();
		$redisWord=array();
		foreach ($Fetch as  $one) {
			# code...
			$redisWord[] = array(
							$one["id"],
							$one["word"],
							$one["type"],
							$one["grammar"],
							$one["parent"],
							$one["mean"],
							$one["note"],
							$one["factors"],
							$one["factormean"],
							$one["status"],
							$one["confidence"],
							$one["creator_id"],
							$one["source"],
							$one["language"]
							);
		}
		$redisData = json_encode($redisWord,JSON_UNESCAPED_UNICODE);
		Redis::hSet("dict/user",$word['word'],$redisData);
		$redisData1 = Redis::hGet("dict/user",$word['word']);

		#更新redis结束
	}

}
