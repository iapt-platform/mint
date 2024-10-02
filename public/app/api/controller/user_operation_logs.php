<?php
require_once __DIR__."/../model/user_operation_logs.php";
require_once __DIR__."/../model/user_operation_frames.php";
require_once __DIR__."/../model/user_operation_dailys.php";
require_once __DIR__."/controller.php";


Class CtlUserOperationLog extends Controller{
	public function index(){
		$result=false;
		switch ($_GET["view"]) {
			case 'day':
				# code...
				$result = UserOperationDaily::where('user_id', $_COOKIE["user_id"])
											->get();		
				$count = UserOperationDaily::where('user_id', $_COOKIE["user_id"])
											->count();
				break;
			case 'frame':
				$result = UserOperationFrame::where('user_id', $_COOKIE["user_id"])
									->get();		
				$count = UserOperationFrame::where('user_id', $_COOKIE["user_id"])
				->count();
				break;
			case 'log':
				$result = UserOperationLog::where('user_id', $_COOKIE["user_id"])
											->whereBeteen()
											->get();
				$count = UserOperationLog::where('user_id', $_COOKIE["user_id"])
											->count();
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
	public function create($op_type = "", $content = null){
		if(!isset($_COOKIE["user_id"])){
			$this->error("not login");
			return false;
		}

		if(isset($_POST["data"])){
			$_data = json_decode($_POST["data"],true);
		}else{
			$_data = ['op_type'=>$op_type,"content"=>$content];
		}
		#获取客户端时区偏移 beijing = +8
		if(!isset($_data['timezone'])){
			if (isset($_COOKIE["timezone"])) {
				$_data['timezone'] = (0 - (int) $_COOKIE["timezone"]) * 60 * 1000;
			} else{
				$_data['timezone'] = 0;
			}	
		}
		$_data["user_id"] = $_COOKIE["user_id"];
		UserOperationLog::insert($_data);


		
	}	
		
	public function show(){
		$result = UserOperationLog::find($_GET["id"]);
		if($result){
			$this->ok($result);
		}else{
			$this->error("没有查询到数据");
		}
	}
	/*
	更新系统wbw汇总表
	*/
}