<?php
require '../vendor/autoloader.php';
$server = $_GET["server"];
$localhost = $_GET["localhost"];
$path=$_GET["path"];
$time=$_GET["time"];

$message="";

$response=["message"=>"","time"=>0,"row"=>0];

$client = new \GuzzleHttp\Client();

$response = $client->request('POST', $server.'/app/'.$path,['verify' => false,'form_params'=>['op'=>'sync','time'=>$time]]);
$serverJson=$response->getBody();
$serverData = json_decode($serverJson);
$response["row"]=count($serverData);
if($response["row"]>0){
	$response["time"]=$serverData[$response["row"]-1]->modify_time;
}
else{
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
	exit;
}
$aIdList=array();
foreach($serverData as $sd){
	$aIdList[]=$sd->guid;
}
$sIdlist = implode(",",$aIdList);
// 拉 id 列表
$response = $client->request('POST', $localhost.'/app/'.$path,['verify' => false,'form_params'=>['op'=>'sync','id'=>$sIdlist]]);
$strLocalData = $response->getBody();
$localData = json_decode($strLocalData);

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
			$update_to_server[]=$sd->guid;
		}
	}
	else{
		//服务器新增 new recorder in server
		$insert_to_local[]=$sd->guid;
	}
}

foreach($localindex as  $x=>$x_value){
	if($x_value[1]==false){
		//客户端新增 new data in local;
		$insert_to_server[]=$x;
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
		
		$idInLocal = json_encode($insert_to_server, JSON_UNESCAPED_UNICODE);
		$response = $client->request('POST', $localhost.'/app/'.$path,['verify' => false,'form_params'=>['op'=>'get','id'=>"{$idInLocal}"]]);
		$localData=$response->getBody();
		$response = $client->request('POST', $server.'/app/'.$path,['verify' => false,'form_params'=>['op'=>'insert','data'=>"{$localData}"]]);
		$message .=  $response->getBody()."<br>";
		
	}

	if(count($update_to_server)>0){
		/*

		*/
		$message .=  "需要更新到服务器".count($update_to_server)."条记录<br>";
		
		$idInLocal = json_encode($update_to_server, JSON_UNESCAPED_UNICODE);
		$response = $client->request('POST', $localhost.'/app/'.$path,['verify' => false,'form_params'=>['op'=>'get','id'=>"{$idInLocal}"]]);
		$localData=$response->getBody();
		$response = $client->request('POST', $server.'/app/'.$path,['verify' => false,'form_params'=>['op'=>'update','data'=>"{$localData}"]]);
		$message .=  $response->getBody()."<br>";
		
	}

	if(count($insert_to_local)>0){

		$message .=  "需要新增到本地".count($insert_to_local)."条记录<br>";
		
		$idInServer = json_encode($insert_to_local, JSON_UNESCAPED_UNICODE);
		$response = $client->request('POST', $server.'/app/'.$path,['verify' => false,'form_params'=>['op'=>'get','id'=>"{$idInServer}"]]);
		$serverData=$response->getBody();
		$response = $client->request('POST', $localhost.'/app/'.$path, ['verify' => false,'form_params'=>['op'=>'insert','data'=>"{$serverData}"]]);
		$message .=  $response->getBody()."<br>";
		
		
	}

	if(count($update_to_local)>0){

		$message .=  "需要更新到本地".count($update_to_local)."条记录<br>";
		
		$idInServer = json_encode($update_to_local, JSON_UNESCAPED_UNICODE);
		$response = $client->request('POST', $server.'/app/'.$path,['verify' => false,'form_params'=>['op'=>'get','id'=>"{$idInServer}"]]);
		$serverData=$response->getBody();
		$response = $client->request('POST', $localhost.'/app/'.$path,['verify' => false,'form_params'=>['op'=>'update','data'=>"{$serverData}"]]);
		$message .=  $response->getBody()."<br>";
		
	}
	
}
$response["message"]=$message;

?>