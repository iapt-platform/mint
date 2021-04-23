<?php
require '../vendor/autoloader.php';
require_once '../redis/function.php';


$server = $_GET["server"];
$localhost = $_GET["localhost"];
$path=$_GET["path"];
$time=$_GET["time"];
$size=$_GET["size"];

$message="";

$output=["message"=>"","time"=>0,"src_row"=>0];

$redis=redis_connect();
if($redis){
	$sync_key = $redis->hget("sync://key",$_COOKIE["userid"]);
	if($sync_key===FALSE){
		$message.= "客户端没有钥匙"."<br>";
		echo json_encode($output, JSON_UNESCAPED_UNICODE);
		exit;
	}
}
else{
	$message.= "redis连接失败"."<br>";
	echo json_encode($output, JSON_UNESCAPED_UNICODE);
	exit;
}

$client = new \GuzzleHttp\Client();
$response = $client->request('POST', $server.'/app/'.$path,['verify' => false,'form_params'=>['op'=>'sync','time'=>$time,'size'=>$size,"key"=>$sync_key]]);
$serverJson=$response->getBody();
$serverData = json_decode($serverJson);
$output["src_row"]=count($serverData);
$message.= "输入时间:".$time."<br>";
$message.= "src_row:".$output["src_row"]."<br>";
if($output["src_row"]>0){
	$output["time"]=$serverData[$output["src_row"]-1]->modify_time;
	$message.= "最新时间:".$output["time"]."<br>";
}
else{
    echo json_encode($output, JSON_UNESCAPED_UNICODE);
	exit;
}
$aIdList=array();
foreach($serverData as $sd){
	$aIdList[]=$sd->guid;
}
$sIdlist = json_encode($aIdList, JSON_UNESCAPED_UNICODE);;
// 拉 id 列表
$response = $client->request('POST', $localhost.'/app/'.$path,['verify' => false,'form_params'=>['op'=>'sync','id'=>$sIdlist,'size'=>$size,"key"=>$sync_key]]);
$strLocalData = $response->getBody();
$localData = json_decode($strLocalData);
$localCount = count($localData);
$message .= "local-row:".$localCount."<br>";
$localindex=array();

$insert_to_server=array();
$update_to_server=array();
$insert_to_local=array();
$update_to_local=array();
$message .= "<h3>{$path}</h3>";
foreach($localData as $local){
	$localindex[$local->guid][0]=$local->modify_time;
	$localindex[$local->guid][1]=false;
}
foreach($serverData as $sd){
	if(isset($localindex[$sd->guid])){
		$localindex[$sd->guid][1]=true;
		if($sd->modify_time>$localindex[$sd->guid][0]){
			//服务器数据较新 server data is new than local
			$update_to_local[]=$sd->guid;
		}
		else if($sd->modify_time==$localindex[$sd->guid][0]){
			//"相同 same
		}
		else{
			//"服务器数据较旧 local data is new than server
			//$update_to_server[]=$sd->guid;
		}
	}
	else{
		//本地没有 新增 insert recorder in local
		$insert_to_local[]=$sd->guid;
	}
}

foreach($localindex as  $x=>$x_value){
	if($x_value[1]==false){
		//服务器新增 new data in server;
		//$insert_to_server[]=$x;
	}
}

$syncCount = count($insert_to_server)+count($update_to_server)+count($insert_to_local)+count($update_to_local);
if($syncCount==0){
	$message .=  "与服务器数据完全一致，无需更新。<br>";
}
else{
//start sync
	if(count($insert_to_server)>0){
		/*

		*/
		$message .=  "需要插入服务器".count($insert_to_server)."条记录<br>";
		/*
		$idInLocal = json_encode($insert_to_server, JSON_UNESCAPED_UNICODE);
		$response = $client->request('POST', $localhost.'/app/'.$path,['verify' => false,'form_params'=>['op'=>'get','id'=>"{$idInLocal}"]]);
		$localData=$response->getBody();
		$response = $client->request('POST', $server.'/app/'.$path,['verify' => false,'form_params'=>['op'=>'insert','data'=>"{$localData}"]]);
		$message .=  $response->getBody()."<br>";
		*/
		
	}

	if(count($update_to_server)>0){
		/*

		*/
		/*
		$message .=  "需要更新到服务器".count($update_to_server)."条记录<br>";
		
		$idInLocal = json_encode($update_to_server, JSON_UNESCAPED_UNICODE);
		$response = $client->request('POST', $localhost.'/app/'.$path,['verify' => false,'form_params'=>['op'=>'get','id'=>"{$idInLocal}"]]);
		$localData=$response->getBody();
		$response = $client->request('POST', $server.'/app/'.$path,['verify' => false,'form_params'=>['op'=>'update','data'=>"{$localData}"]]);
		$message .=  $response->getBody()."<br>";
		*/
		
	}

	if(count($insert_to_local)>0){
		$message .=  "需要新增到本地".count($insert_to_local)."条记录<br>";
		/*
		$idInServer = json_encode($insert_to_local, JSON_UNESCAPED_UNICODE);
		$response = $client->request('POST', $server.'/app/'.$path,['verify' => false,'form_params'=>['op'=>'get','id'=>"{$idInServer}","key"=>$sync_key]]);
		$serverData=$response->getBody();
		$response = $client->request('POST', $localhost.'/app/'.$path, ['verify' => false,'form_params'=>['op'=>'insert','data'=>"{$serverData}","key"=>$sync_key]]);
		$message .=  $response->getBody()."<br>";
		*/
		
	}

	if(count($update_to_local)>0){
		$message .=  "需要更新到本地".count($update_to_local)."条记录<br>";
		/*
		$idInServer = json_encode($update_to_local, JSON_UNESCAPED_UNICODE);
		$response = $client->request('POST', $server.'/app/'.$path,['verify' => false,'form_params'=>['op'=>'get','id'=>"{$idInServer}","key"=>$sync_key]]);
		$serverData=$response->getBody();
		$response = $client->request('POST', $localhost.'/app/'.$path,['verify' => false,'form_params'=>['op'=>'update','data'=>"{$serverData}","key"=>$sync_key]]);
		$message .=  $response->getBody()."<br>";
		*/
		
	}
	
}
$output["message"]=$message;
echo json_encode($output, JSON_UNESCAPED_UNICODE);


?>