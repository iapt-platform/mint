<?php
//查询term字典

require_once "../path.php";
require_once "../public/_pdo.php";
require_once '../public/load_lang.php';
require_once '../public/function.php';

//is login
if(isset($_COOKIE["username"])){
	$username = $_COOKIE["username"];
}
else{
	$username = "";
}

if(isset($_GET["op"])){
	$op=$_GET["op"];
}
else if(isset($_POST["op"])){
	$op=$_POST["op"];
}
if(isset($_GET["word"])){
	$word=mb_strtolower($_GET["word"],'UTF-8');
	$org_word=$word;
}

if(isset($_GET["guid"])){
	$_guid=$_GET["guid"];
}

if(isset($_GET["username"])){
	$username=$_GET["username"];
}

global $PDO;
PDO_Connect("sqlite:"._FILE_DB_TERM_);
switch($op){
	case "pre"://预查询
	{
		$query = "select word,meaning from term where \"eword\" like ".$PDO->quote($word.'%')." OR \"word\" like ".$PDO->quote($word.'%')." group by word limit 0,10";
		$Fetch = PDO_FetchAll($query);
		if(count($Fetch)<5){
			$query = "select word,meaning from term where \"eword\" like ".$PDO->quote('%'.$word.'%')." OR \"word\" like ".$PDO->quote('%'.$word.'%')." group by word limit 0,10";
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
			if(count($Fetch)<8){
				$query = "select word,meaning from term where \"meaning\" like ".$PDO->quote($word.'%')." OR \"other_meaning\" like ".$PDO->quote($word.'%')." group by word limit 0,10";
				$Fetch3 = PDO_FetchAll($query);
				
				$Fetch = array_merge($Fetch,$Fetch3);
				if(count($Fetch)<8){
					$query = "select word,meaning from term where \"meaning\" like ".$PDO->quote('%'.$word.'%')." OR \"other_meaning\" like ".$PDO->quote('%'.$word.'%')." group by word limit 0,10";
					$Fetch4 = PDO_FetchAll($query);
					//去掉重复的
					foreach($Fetch4 as $onerow){
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
		}
		echo json_encode($Fetch, JSON_UNESCAPED_UNICODE);
		break;
	}
	case "my":
	{
		$query = "select guid,word,meaning,other_meaning from term  where owner= ".$PDO->quote($username);
		$Fetch = PDO_FetchAll($query);
		$iFetch=count($Fetch);
		if($iFetch>0){
			echo json_encode($Fetch, JSON_UNESCAPED_UNICODE);
		}
		break;
	}
	case "allpali":
	{
		$query = "select word from term  where 1 group by word";
		$Fetch = PDO_FetchAll($query);
		$iFetch=count($Fetch);
		if($iFetch>0){
			echo json_encode($Fetch, JSON_UNESCAPED_UNICODE);
		}
		break;
	}
	case "allmean":
	{
		$query = "select meaning from term  where \"word\" = ".$PDO->quote($word)." group by meaning";
		$Fetch = PDO_FetchAll($query);
		foreach($Fetch as $one){
			echo "<a>".$one["meaning"]."</a> ";
		}
		//echo json_encode($Fetch, JSON_UNESCAPED_UNICODE);
		break;
	}
	case "load_id":
	{
		if(isset($_GET["id"])){
			$id=$_GET["id"];
			$query = "select * from term  where \"guid\" = ".$PDO->quote($id);
			$Fetch = PDO_FetchAll($query);
			echo json_encode($Fetch, JSON_UNESCAPED_UNICODE);			
		}
		else{
			echo json_encode(array(), JSON_UNESCAPED_UNICODE);	
		}
		break;
	}
	case "search":
	{
		if(!isset($word)){
			return;
		}
		if(trim($word)==""){
			return;
		}
		echo"<div class='pali'>{$word}</div>";
		//查本人数据
		echo "<div></div>";//My Term
		$query = "select * from term  where \"word\" = ".$PDO->quote($word)." AND \"owner\"= ".$PDO->quote($username)." limit 0,30";
		$Fetch = PDO_FetchAll($query);
		$iFetch=count($Fetch);
		$count_return+=$iFetch;
		if($iFetch>0){
			for($i=0;$i<$iFetch;$i++){
				$mean=$Fetch[$i]["meaning"];
				$guid=$Fetch[$i]["guid"];
				$dict_list[$guid]=$Fetch[$i]["owner"];
				echo "<div class='dict_word'>";
				echo "<a name='ref_dict_$guid'></a>";
				echo "<div id='term_dict_my_$guid'>";
				echo "<div class='dict'>Your</div>";
				echo "<div class='tag'>{$Fetch[$i]["tag"]}</div>";
				echo "<div class='mean'>".$mean."</div>";
				echo "<div class='other_mean'>".$Fetch[$i]["other_meaning"]."</div>";
				echo "<div class='term_note' status=0>".$Fetch[$i]["note"]."</div>";
				echo "</div>";
				//编辑词条表单
				echo "<div id='term_dict_my_edit_$guid' style='display:none'>";
				echo "<input type='hidden' id='term_edit_word_$guid' value='$word' />";
				echo "<div class='mean'><input type='input' id='term_edit_mean_{$guid}'  placeholder='{$_local->gui->meaning}' value='$mean' /></div>";//'意思'
				echo "<div class='other_mean'><input type='input' id='term_edit_mean2_{$guid}'  placeholder='{$_local->gui->other_meaning}' value='".$Fetch[$i]["other_meaning"]."' /></div>";//'备选意思（可选项）'
				echo "<div class='tag'><input type='input' id='term_edit_tag_{$guid}'  placeholder='{$_local->gui->other_tag}' value='".$Fetch[$i]["tag"]."' /></div>";//'备选意思（可选项）'
				echo "<div class='note'><textarea  id='term_edit_note_$guid'  placeholder='".$module_gui_str['editor']['1043']."'>".$Fetch[$i]["note"]."</textarea></div>";//'注解'
				echo "</div>";
				echo "<div id='term_edit_btn1_$guid'>";
				//echo "<button onclick=\"term_apply('$guid')\">{$_local->gui->apply}</button>";//Apply
				echo "<button onclick=\"term_edit('$guid')\">{$_local->gui->edit}</button>";//Edit
				echo "</div>";
				echo "<div id='term_edit_btn2_{$guid}'  style='display:none'>";
				echo "<button onclick=\"term_data_esc_edit('$guid')\">{$_local->gui->cancel}</button>";//Cancel
				echo "<button onclick=\"term_data_save('$guid')\">{$_local->gui->save}</button>";//保存
				echo "</div>";
				echo "</div>";
			}
		}
		//新建词条
		echo "<div class='dict_word'>";
		echo "<button onclick=\"term_show_new()\">{$_local->gui->new}</button>";
		echo "<div id='term_new_recorder' style='display:none;'>";
		echo "<div class='dict'>".$module_gui_str['editor']['1121']."</div>";//New Techinc Term
		echo "<div class='mean'>{$_local->gui->pāli}：<input type='input' placeholder='{$_local->gui->pāli}' id='term_new_word' value='{$word}' /></div>";//'拼写'
		echo "<div class='mean'>{$_local->gui->meaning}<input type='input' placeholder='{$_local->gui->meaning}' id='term_new_mean'/></div>";//'意思'
		echo "<div class='other_mean'>{$_local->gui->other_meaning}：<input type='input'  placeholder='{$_local->gui->other_meaning}' id='term_new_mean2'/></div>";//'备选意思（可选项）'
		echo "<div class='tag'>{$_local->gui->tag}：<input type='input'  placeholder='{$_local->gui->tag}' id='term_new_tag'/></div>";//'标签'
		echo "<div class='note'>{$_local->gui->note}：<textarea width='100%' height='3em'  placeholder='{$_local->gui->note}' id='term_new_note'></textarea></div>";//'注解'
		echo "<button onclick=\"term_data_save('')\">{$_local->gui->save}</button>";//保存
		echo "</div>";
		echo "</div>";

		
		//查他人数据
		$query = "select * from term  where \"word\" = ".$PDO->quote($word)."AND \"owner\" <> ".$PDO->quote($username)." limit 0,30";
		
		$Fetch = PDO_FetchAll($query);
		$iFetch=count($Fetch);
		$count_return+=$iFetch;
		if($iFetch>0){
			for($i=0;$i<$iFetch;$i++){
				$mean=$Fetch[$i]["meaning"];
				$guid=$Fetch[$i]["guid"];
				$dict_list[$guid]=$Fetch[$i]["owner"];
				echo "<div class='dict_word'>";
				echo "<a name='ref_dict_$guid'></a>";
				echo"<div class='dict'>".$Fetch[$i]["owner"]."</div>";
				echo "<div class='mean'>".$mean."</div>";
				echo "<div class='other_mean'>".$Fetch[$i]["other_meaning"]."</div>";
				echo "<div class='term_note'>".$Fetch[$i]["note"]."</div>";
				echo "<button onclick=\"term_data_copy_to_me($guid)\">{$_local->gui->copy}</button>";//复制
				echo "</div>";
			}
		}

	
		//查内容
		/*
		if($count_return<2){
			$word1=$org_word;
			$wordInMean="%$org_word%";
			echo $module_gui_str['editor']['1124']."：$org_word<br />";
			$query = "select * from term  where \"meaning\" like ".$PDO->quote($word)." limit 0,30";
			$Fetch = PDO_FetchAll($query);
			$iFetch=count($Fetch);
			$count_return+=$iFetch;
			if($iFetch>0){
				for($i=0;$i<$iFetch;$i++){
					$mean=$Fetch[$i]["meaning"];
					$pos=mb_stripos($mean,$word,0,"UTF-8");
					if($pos){
						if($pos>20){
							$start=$pos-20;
						}
						else{
							$start=0;
						}
						$newmean=mb_substr($mean,$start,100,"UTF-8");
					}
					else{
						$newmean=$mean;
					}
					$pos=mb_stripos($newmean,$word1,0,"UTF-8");
					$head=mb_substr($newmean,0,$pos,"UTF-8");
					$mid=mb_substr($newmean,$pos,mb_strlen($word1,"UTF-8"),"UTF-8");
					$end=mb_substr($newmean,$pos+mb_strlen($word1,"UTF-8"),NULL,"UTF-8");
					$heigh_light_mean="$head<hl>$mid</hl>$end";
					$outXml = "<div class='dict_word'>";
					$outXml = $outXml."<div class='dict'>".$Fetch[$i]["owner"]."</div>";					
					$outXml = $outXml."<div class='pali'>".$Fetch[$i]["word"]."</div>";
					$outXml = $outXml."<div class='mean'>".$heigh_light_mean."</div>";
					$outXml = $outXml."<div class='note'>{$Fetch[$i]["note"]}</div>";
					$outXml = $outXml."</div>";
					echo $outXml;
				}
			}		
		}
		*/
		//查内容结束

		echo "<div id='dictlist'>";
		echo "</div>";
		
		break;
	}
	case "save":
	{
		$currTime=sprintf("%d",microtime(true)*1000);
		if(isset($_GET["modify_time"])){
			$mTime=$_GET["modify_time"];
		}
		else{
			$mTime=time();
		}
		if($_GET["guid"]!=""){
			$mean=$_GET["mean"];
			$query="UPDATE term SET meaning='$mean' ,
									other_meaning='".$_GET["mean2"]."' ,
									tag='".$_GET["tag"]."' ,
									receive_time='".time()."' ,
									modify_time='$mTime' ,
									note='".$_GET["note"]."' 
							where guid='".$_GET["guid"]."'";
		}
		else{
			$newGuid=UUID::v4();
			$word=$_GET["word"];
			$worden=pali2english($word);
			$mean=$_GET["mean"];
			$mean2=$_GET["mean2"];
			$note=$_GET["note"];
			$tag=$_GET["tag"];
			$time=time();
			$query="INSERT INTO term VALUES (NULL, 
											'$newGuid', 
											'$word', 
											'$worden', 
											'$mean', 
											'$mean2', 
											'$note', 
											'$tag', 
											'$time', 
											'$username', 
											'1',
											'zh',
											'$time',
											'$time')";		
		}
			$stmt = @PDO_Execute($query);
			$respond=array("status"=>0,"message"=>"");
			if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
				$error = PDO_ErrorInfo();
				$respond['status']=1;
				$respond['message']=$error[2].$query;
			}
			else{
				$respond['status']=0;
				$respond['message']=$word;
			}		
			echo json_encode($respond, JSON_UNESCAPED_UNICODE);
		break;
	}
	case "copy"://拷贝到我的字典
	{
		$query = "select * from term  where \"guid\" = ".$PDO->quote($_GET["wordid"]);
		
		$Fetch = PDO_FetchAll($query);
		$iFetch=count($Fetch);
		if($iFetch>0){
			/* 开始一个事务，关闭自动提交 */
			$PDO->beginTransaction();
			$query="INSERT INTO term ('id','guid','word','word_en','meaning','other_meaning','note','tag','create_time','owner','hit') VALUES (null,?,?,?,?,?,?,?,".time().",'$username',1)";
			$stmt = $PDO->prepare($query);
			{
			$stmt->execute(array(UUID::v4,
								$Fetch[0]["word"],
								$Fetch[0]["word_en"],
								$Fetch[0]["meaning"],
								$Fetch[0]["other_meaning"],
								$Fetch[0]["note"],
								$Fetch[0]["tag"],
								));
			}
			/* 提交更改 */
			$PDO->commit();
			if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
				$error = PDO_ErrorInfo();
				echo "error - $error[2] <br>";
			}
			else{
				echo "updata ok.";
			}			
		}
		break;
	}
	case "extract":
	{
		if(isset($_POST["words"])){
			$words=$_POST["words"];
		}
		if(isset($_POST["authors"])){
			$authors=str_getcsv($_POST["authors"]);
		}		
		$query = "select * from term  where \"word\" in {$words}  limit 0,1000";
		$Fetch = PDO_FetchAll($query);
		$iFetch=count($Fetch);
		echo json_encode($Fetch, JSON_UNESCAPED_UNICODE);
		break;
	}
	case "sync":
	{
		$time=$_GET["time"];
		$query = "select guid,modify_time from term  where receive_time>'{$time}' limit 0,1000";
		$Fetch = PDO_FetchAll($query);
		$iFetch=count($Fetch);
		echo json_encode($Fetch, JSON_UNESCAPED_UNICODE);
		break;
	}
	case "get":
	{
		$Fetch = array();
		if(isset($guid)){
			$query = "select * from term  where \"guid\" = '{$guid}'";
		}
		else if(isset($word)){
			$query = "select * from term  where \"word\" = '{$word}'";
		}
		else{
			echo "[]";
			return;
		}
		$Fetch = PDO_FetchAll($query);
		$iFetch=count($Fetch);
		echo json_encode($Fetch, JSON_UNESCAPED_UNICODE);

		break;
	}
	
}


?>