<?php
//获取书的目录-索引 包含缩减的正文
include "../public/config.php";
include "../public/_pdo.php";

	$currBook=$_GET["book"];
	if(substr($currBook,0,1)=="p"){
		$currBook = substr($currBook,1);
	}



	echo "<index>";

	//open database
	PDO_Connect("sqlite:$_file_db_pali_text");
	$query = "select * FROM pali_text where \"book\"=".$PDO->quote($currBook);

	$Fetch = PDO_FetchAll($query);
	$iFetch=count($Fetch);

	if($iFetch>0){
		for($i=0;$i<$iFetch;$i++){
			$level=$Fetch[$i]["level"];
			if($level==100){
				$level=0;
			}
			echo "<paragraph>";
			echo "<book>{$Fetch[$i]["book"]}</book>";
			echo "<par>{$Fetch[$i]["paragraph"]}</par>";
			echo "<level>{$level}</level>";
			echo "<class>".$Fetch[$i]["class"]."</class>";
			echo "<title>{$Fetch[$i]["toc"]}</title>";
			echo "<language>".$Fetch[$i]["language"]."</language>";
			echo "<author>".$Fetch[$i]["author"]."</author>";
			echo "<edition>".$Fetch[$i]["edition"]."</edition>";
			echo "<subver></subver>";
			echo "</paragraph>";
		}
	}
	/*查询结束*/
	echo  "</index>";
?>