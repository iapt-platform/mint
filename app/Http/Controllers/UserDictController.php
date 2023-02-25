<?php

namespace App\Http\Controllers;

use App\Models\UserDict;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use App\Http\Api;
use App\Http\Api\AuthApi;

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
		$indexCol = ['id','word','type','grammar','mean','parent','note','factors','confidence','updated_at','creator_id'];
		switch ($request->get('view')) {
            case 'studio':
				# 获取studio内所有channel
                $user = \App\Http\Api\AuthApi::current($request);
                if($user){
                    //判断当前用户是否有指定的studio的权限
                    if($user['user_uid'] === \App\Http\Api\StudioApi::getIdByName($request->get('name'))){
                        $table = UserDict::select($indexCol)
                                    ->where('creator_id', $user["user_id"])
                                    ->where('source', "_USER_WBW_");
                    }else{
                        return $this->error(__('auth.failed'));
                    }
                }else{
                    return $this->error(__('auth.failed'));
                }
				break;
			case 'user':
				# code...
				$table = UserDict::select($indexCol)
									->where('creator_id', $_COOKIE["user_id"])
									->where('source', '<>', "_SYS_USER_WBW_");

				break;
			case 'word':
				$table = UserDict::select($indexCol)
									->where('word', $_GET["word"]);
				break;
			default:
				# code...
				break;
		}
        if(isset($_GET["search"])){
            $table->where('word', 'like', $_GET["search"]."%");
        }
        if(isset($_GET["order"]) && isset($_GET["dir"])){
            $table->orderBy($_GET["order"],$_GET["dir"]);
        }else{
            $table->orderBy('updated_at','desc');
        }
        $count = $table->count();
        if(isset($_GET["limit"])){
            $offset = 0;
            if(isset($_GET["offset"])){
                $offset = $_GET["offset"];
            }
            $table->skip($offset)->take($_GET["limit"]);
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
        //
		if(!isset($_COOKIE["user_id"])){
			$this->error("not login");
		}

		$_data = json_decode($_POST["data"],true);
		switch($request->get('view')){
			case "wbw":
				#查询用户重复的数据
				$iOk = 0;
				$updateOk=0;
				foreach ($_data as $key => $word) {
					# code...
					$isDoesntExist = UserDict::where('creator_id', $_COOKIE["user_id"])
										->where('word',$word["word"])
										->where('type',$word["type"])
										->where('grammar',$word["grammar"])
										->where('parent',$word["parent"])
										->where('mean',$word["mean"])
										->where('factors',$word["factors"])
										->where('factormean',$word["factormean"])
										->where('source','_USER_WBW_')
										->doesntExist();

					if($isDoesntExist){
						#不存在插入数据
						$word["id"]=app('snowflake')->id();
						$word["source"]='_USER_WBW_';
						$word["create_time"]=mTime();
						$word["creator_id"]=$_COOKIE["user_id"];
						$id = UserDict::insert($word);
						$updateOk = $this->update_sys_wbw($word);
						$this->update_redis($word);
						$iOk++;
					}
				}
				$this->ok([$iOk,$updateOk]);
				break;
			case "dict":
				break;
		}
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
        Log::info("id={$id}");
        Log::info($newData);
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
		Log::info("userDictController->destroy start");
		Log::info("userDictController->destroy id= {$id}");

        if(isset($_COOKIE["user_id"])){
            $user_id = $_COOKIE["user_id"];
        }else{
            $user = AuthApi::current($request);
            if(!$user){
                return $this->error(__('auth.failed'));
            }
            $user_id = $user['user_id'];
        }
        if($request->has("id")){
            $arrId = json_decode($request->get("id"),true) ;
            $count = 0;
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
		Log::info("userDictController->delete start");
		$arrId = json_decode($request->get("id"),true) ;
		Log::info("id=".$request->get("id"));
		$count = 0;
		$updateOk = false;
		foreach ($arrId as $key => $id) {
			$data = UserDict::where('id',$id)->first();
			if($data){
				# 找到对应数据
				Log::info('creator_id:'.$data->creator_id);
				$param = [
					"id"=>$id,
					'creator_id'=>$_COOKIE["user_id"]
				];
				Log::info($param);
				$del = UserDict::where($param)->delete();
				$count += $del;
				$updateOk = $this->update_sys_wbw($data);
				$this->update_redis($data);
			}
		}
		Log::info("delete:".$count);
		return $this->ok(['deleted'=>$count]);
	}

	/*
	更新系统wbw汇总表
	*/
	private function update_sys_wbw($data){

		#查询用户重复的数据
		$count = UserDict::where('word',$data["word"])
		->where('type',$data["type"])
		->where('grammar',$data["grammar"])
		->where('parent',$data["parent"])
		->where('mean',$data["mean"])
		->where('factors',$data["factors"])
		->where('factormean',$data["factormean"])
		->where('source','_USER_WBW_')
		->count();

		if($count==0){
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
							->where('source','_USER_WBW_')
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
            if($count==0){
                #系统字典没有 新增
                $result = UserDict::insert(
				[
                    'id' =>$snowflake->id(),
					'word'=>$data["word"],
					'type'=>$data["type"],
					'grammar'=>$data["grammar"],
					'parent'=>$data["parent"],
					'mean'=>$data["mean"],
					'factors'=>$data["factors"],
					'factormean'=>$data["factormean"],
					'source'=>"_SYS_USER_WBW_",
                    'creator_id' => $creator_id,
					'ref_counter' => 1,
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
		Log::info("word={$word['word']} redis-data={$redisData}");
		Redis::hSet("dict/user",$word['word'],$redisData);
		$redisData1 = Redis::hGet("dict/user",$word['word']);
		Log::info("word={$word['word']} redis-data1={$redisData1}");

		#更新redis结束
	}

}
