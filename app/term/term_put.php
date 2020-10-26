<?php
		$currTime=sprintf("%d",microtime(true)*1000);
		if(isset($_GET["modify_time"])){
			$mTime=$_GET["modify_time"];
		}
		else{
			$mTime=mTime();
		}
		if($_GET["guid"]!=""){
			$query="UPDATE term SET meaning= ? ,other_meaning = ? , tag= ? ,channal = ? ,  language = ? , note = ? , receive_time= ?, modify_time= ?   where guid= ? ";
			$stmt = @PDO_Execute($query,array($_GET["mean"],
																		$_GET["mean2"],
																		$_GET["tag"],
																		$_GET["channal"],
																		$_GET["language"],
																		$_GET["note"],
																		mTime(),
																		$mTime,
																		$_GET["guid"]
																	));
		}
		else{
			$parm = array();
			$parm[]=UUID::v4();
			$parm[]=$_GET["word"];
			$parm[]=pali2english($word);
			$parm[]=$_GET["mean"];
			$parm[]=$_GET["mean2"];
			$parm[]=$_GET["tag"];			
			$parm[]=$_GET["channal"];			
			$parm[]=$_GET["language"];			
			$parm[]=$_GET["note"];
			$parm[]=$_COOKIE["userid"];
			$parm[]=0;
			$parm[]=mTime();
			$parm[]=mTime();
			$parm[]=mTime();
			$query="INSERT INTO term (id, guid, word, word_en, meaning, other_meaning, tag, channal, language,note,owner,hit,create_time,modify_time,receive_time ) 
															VALUES (NULL, ? , ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) "; 

			$stmt = @PDO_Execute($query,$parm);
		}
			
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

?>