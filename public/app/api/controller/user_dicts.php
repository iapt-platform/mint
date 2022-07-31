<?php
require_once __DIR__."/../model/user_dicts.php";
require_once __DIR__."/controller.php";
require_once __DIR__."/../../public/snowflakeid.php";
require_once __DIR__."/../../public/function.php";

Class CtlUserDict extends Controller{
	public function index(){
		$result=false;
		$indexCol = ['id','word','type','grammar','mean','factors','confidence','updated_at'];
		switch ($_GET["view"]) {
			case 'user':
				# code...
				$table = UserDict::select($indexCol)
									->where('creator_id', $_COOKIE["user_id"])
									->where('source', '<>', "_SYS_USER_WBW_");
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
				break;
			case 'word':
				$result = UserDict::select($indexCol)
									->where('word', $_GET["word"])
									->orderBy('created_at','desc')
									->get();				
				break;
			default:
				# code...
				break;
		}
		if($result){
			$this->ok(["rows"=>$result,"count"=>$count]);
		}else{
			$this->error("没有查询到数据");
		}

	}	
	public function create(){
		if(!isset($_COOKIE["user_id"])){
			$this->error("not login");
		}
        $snowflake = new SnowFlakeId();

		$_data = json_decode($_POST["data"],true);
		switch($_POST["view"]){
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
						$word["id"]=$snowflake->id();
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
		
	public function show(){

		$result = UserDict::find($_GET["id"]);
		if($result){
			$this->ok($result);
		}else{
			$this->error("没有查询到数据");
		}
	}
	public function update(){
        $snowflake = new SnowFlakeId();
		$data = json_decode($_POST["data"],true);

		$result = UserDict::where('id', $data["id"])
						  ->update($data);
		if($result){
			$updateOk = $this->update_sys_wbw($data);
			$this->update_redis($data);
			$this->ok([$result,$updateOk]);
		}else{
			$this->error("没有查询到数据");
		}
	}	
	public function delete(){
		$arrId = json_decode($_GET["id"],true) ;
		$count = 0;
		foreach ($arrId as $key => $id) {
			# code...
			$data = UserDict::find($id);
			$result = UserDict::where('id', $id)
							->where('creator_id', $_COOKIE["user_id"])
							->delete();
			if($result){
				$count++;
				$updateOk = $this->update_sys_wbw($data);	
				$this->update_redis($data);
			}
		}
		$this->ok([$count,$updateOk]);
	}

	/*
	更新系统wbw汇总表
	*/
	private function update_sys_wbw($data){
        $snowflake = new SnowFlakeId();
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
                    "create_time"=>mTime()
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

		if ($this->redis != false) {
			{
				$Fetch = UserDict::where(['word'=>$word['word'],"source"=>"_SYS_USER_WBW_"])->get();
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
				$this->redis->hSet(Redis["prefix"]."dict/user",$word['word'],json_encode($redisWord,JSON_UNESCAPED_UNICODE));			
			}
		}
		#更新redis结束
	}
} 