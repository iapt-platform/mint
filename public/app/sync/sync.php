<?php
require '../vendor/autoloader.php';
require_once '../redis/function.php';


$server = $_GET["server"];
$localhost = $_GET["localhost"];
$path=$_GET["path"];
$time=$_GET["time"];
$size=$_GET["size"];

$output=["message"=>"","time"=>0,"src_row"=>0];
$message = "<h3>正在处理 {$path}</h3>";

$redis=redis_connect();
if($redis){
	$sync_key = $redis->hget("sync://key",$_COOKIE["userid"]);
	if($sync_key===FALSE){
		$message.= "客户端没有钥匙"."<br>";
		$output["message"]=$message;
		echo json_encode($output, JSON_UNESCAPED_UNICODE);
		exit;
	}
}
else{
	$message.= "redis连接失败"."<br>";
	$output["message"]=$message;
	echo json_encode($output, JSON_UNESCAPED_UNICODE);
	exit;
}

$client = new \GuzzleHttp\Client();
if($size<0){
	$response = $client->request('POST', $server.'/app/'.$path,['verify' => false,'form_params'=>['op'=>'sync_count','time'=>$time,'size'=>$size,"key"=>$sync_key,"userid"=>$_COOKIE["userid"]]]);
	$serverJson=(string)$response->getBody();
	$serverData = json_decode($serverJson,true);
	echo json_encode($serverData, JSON_UNESCAPED_UNICODE);
	exit;	
}
$response = $client->request('POST', $server.'/app/'.$path,['verify' => false,'form_params'=>['op'=>'sync','time'=>$time,'size'=>$size,"key"=>$sync_key,"userid"=>$_COOKIE["userid"]]]);
$serverJson=(string)$response->getBody();
$serverData = json_decode($serverJson,true);
if($serverData===NULL){
	$message.="无法解码 数据：".$serverJson;
	$output["message"]=$message;
	echo json_encode($output, JSON_UNESCAPED_UNICODE);
	exit;	
}
if($serverData["error"]>0){
	$message.=$serverData["message"];
	$output["message"]=$message;
	echo json_encode($output, JSON_UNESCAPED_UNICODE);
	exit;
}
$message .= "<div>";
$serverDBData = $serverData["data"];
$output["src_row"]=count($serverDBData);
$message.= "输入时间:".$time." | ";
$message.= "src_row:".$output["src_row"]." | ";
if($output["src_row"]>0){
	$output["time"]=$serverDBData[$output["src_row"]-1]["receive_time"];
	$message.= "最新时间:".$output["time"]." | ";
}
else{
	$message .= "没有查询到数据</div>";
	$output["message"]=$message;
    echo json_encode($output, JSON_UNESCAPED_UNICODE);
	exit;
}
$aIdList=array();
foreach($serverDBData as $sd){
	$aIdList[]=$sd["sync_id"];
}
$sIdlist = json_encode($aIdList, JSON_UNESCAPED_UNICODE);
// 拉 id 列表
$response = $client->request('POST', $localhost.'/app/'.$path,['verify' => false,'form_params'=>['op'=>'sync','id'=>$sIdlist,'size'=>$size,"key"=>$sync_key,"userid"=>$_COOKIE["userid"]]]);
$strLocalData = (string)$response->getBody();
$localData = json_decode($strLocalData,true);
if($localData["error"]>0){
	$message .="client 拉 id 列表 error:". $localData["message"];
	$output["message"]=$message;
	echo json_encode($output, JSON_UNESCAPED_UNICODE);
	exit;
}
$localDBData = $localData["data"];
$localCount = count($localDBData);
$message .= "local-row:".$localCount." | ";
$message .= "</div>";

$localindex=array();

$insert_to_server=array();
$update_to_server=array();
$insert_to_local=array();
$update_to_local=array();
#开始比对数据
foreach($localDBData as $local){
	$localindex[$local["sync_id"]][0]=$local["modify_time"];
	$localindex[$local["sync_id"]][1]=false;
}
foreach($serverDBData as $sd){
	if(isset($localindex[$sd["sync_id"]])){
		$localindex[$sd["sync_id"]][1]=true;
		if($sd['modify_time']>$localindex[$sd["sync_id"]][0]){
			//服务器数据较新 server data is new than local
			$update_to_local[]=$sd["guid"];
		}
		else if($sd['modify_time']==$localindex[$sd["sync_id"]][0]){
			//"相同 same
		}
		else{
			//"服务器数据较旧 local data is new than server
			//$update_to_server[]=$sd->guid;
		}
	}
	else{
		//本地没有 新增 insert recorder in local
		$insert_to_local[]=$sd['guid'];
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

	$message .= "<div>";
	if(count($insert_to_local)>0){
		$message .=  "需要新增到目标机".count($insert_to_local)."条记录 | ";
		
		#提取数据
		$idInServer = json_encode($insert_to_local, JSON_UNESCAPED_UNICODE);
		$response = $client->request('POST', $server.'/app/'.$path,['verify' => false,'form_params'=>['op'=>'get','id'=>"{$idInServer}","key"=>$sync_key,"userid"=>$_COOKIE["userid"]]]);
		$serverData=(string)$response->getBody();
		$arrData = json_decode($serverData,true);
		if($arrData["error"]==0){
			$message .= "数据提取成功：{$arrData["message"]} | ";
			$strData = json_encode($arrData["data"], JSON_UNESCAPED_UNICODE);
			$response = $client->request('POST', $localhost.'/app/'.$path, ['verify' => false,'form_params'=>['op'=>'insert','data'=>"{$strData}","key"=>$sync_key,"userid"=>$_COOKIE["userid"]]]);
			$insertMsg =  (string)$response->getBody();	
			$arrInsertMsg = json_decode($insertMsg,true);	
			$message .= $arrInsertMsg["message"] . " | ";
		}
		else{
			$message .= "数据提取错误 错误信息：{$arrData["message"]} ";
		}
		
	}
	$message .= "</div>";

	$message .= "<div>";
	if(count($update_to_local)>0){
		$message .=  "需要更新到目标机".count($update_to_local)."条记录 | ";
		$idInServer = json_encode($update_to_local, JSON_UNESCAPED_UNICODE);
		$response = $client->request('POST', $server.'/app/'.$path,['verify' => false,'form_params'=>['op'=>'get','id'=>"{$idInServer}","key"=>$sync_key,"userid"=>$_COOKIE["userid"]]]);
		$serverData=(string)$response->getBody();
		$arrData = json_decode($serverData,true);
		if($arrData["error"]==0){
			$message .= "数据提取成功：{$arrData["message"]} | ";
			$strData = json_encode($arrData["data"], JSON_UNESCAPED_UNICODE);
			$response = $client->request('POST', $localhost.'/app/'.$path,['verify' => false,'form_params'=>['op'=>'update','data'=>"{$strData}","key"=>$sync_key,"userid"=>$_COOKIE["userid"]]]);
			$strMsgUpdate =  (string)$response->getBody();
			$arrMsgUpdate = json_decode($strMsgUpdate,true);
			$message .= $arrMsgUpdate["message"] . " | ";;
		}
		else{
			$message .= "数据提取错误 错误信息：{$arrData["message"]} ";
		}
	}
	$message .= "</div>";

}
$output["message"]=$message;
echo json_encode($output, JSON_UNESCAPED_UNICODE);

?>