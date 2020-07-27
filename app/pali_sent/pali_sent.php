<?php
//查询term字典
require_once "../path.php";
require_once "../public/_pdo.php";

//is login
if(isset($_COOKIE["username"]) && !empty($_COOKIE["username"])){
	$username = $_COOKIE["username"];
}
else{
	$username = "";
}

if(isset($_GET["op"])){
	$_op=$_GET["op"];
}
else if(isset($_POST["op"])){
	$_op=$_POST["op"];
}
if(isset($_GET["word"])){
	$_word=mb_strtolower($_GET["word"],'UTF-8');

}

if(isset($_GET["id"])){
	$_id=$_GET["id"];
}

global $PDO;
PDO_Connect("sqlite:"._FILE_DB_PALI_SENTENCE_);
switch($_op){
	case "get":
	{
		$Fetch=array();
		if(isset($_word)){
			$queryWord = str_replace(" ","",$_word);
			$query = "select book,paragraph,text from pali_sent where \"real\" like ".$PDO->quote("%".$queryWord.'%')." limit 0,5";
			$Fetch = PDO_FetchAll($query);
			$newList = array();
			//去掉重复的
			foreach($Fetch as $onerow){
				$found=false;
				foreach($newList as $new){
					if($onerow["text"]==$new["text"]){
						$found=true;
						break;
					}
				}
				if($found==false){
					array_push($newList,$onerow);
				}
			}
			$Fetch = $newList;

			if(count($Fetch)<5){
				$query = "select text from pali_sent where \"real_en\" like ".$PDO->quote('%'.$queryWord.'%')." limit 0,5";
				$Fetch2 = PDO_FetchAll($query);
				//去掉重复的
				foreach($Fetch2 as $onerow){
					$found=false;
					foreach($Fetch as $oldArray){
						if($onerow["word"]==$oldArray["word"]){
							$found=true;
							break;
						}
					}
					if($found==false){
						array_push($Fetch,$onerow);
					}
				}
			}
			
		}
		else if(isset($_id)){
		}
		echo json_encode($Fetch, JSON_UNESCAPED_UNICODE);
		break;
	}
}
?>