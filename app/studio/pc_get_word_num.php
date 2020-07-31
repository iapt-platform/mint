<?php
//获取书的单词信息
include "../public/config.php";
include "../public/_pdo.php";

$get_book=$_GET["book"];
$get_par_begin=$_GET["begin"];
$get_par_end=$_GET["end"];

$db_file = "{$dir_palicanon}templet/p".$get_book."_tpl.db3";
	
		//open database
	PDO_Connect("sqlite:$db_file");
	if($get_par_end==-1){
		echo "0,0,0,0";
		return;
		
		$query1="SELECT count(*) FROM \"main\" WHERE 1";
		$query2="select count(*) from (SELECT count() FROM \"main\" WHERE 1 group by real ) T";

		$query3="SELECT sum(length(real)) FROM \"main\" WHERE 1";
		$query4="select sum(length(real)) from (SELECT count(),real FROM \"main\" WHERE 1 group by real ) T";
		
	}
	else{
		$query1="SELECT count(*) FROM \"main\" WHERE paragraph BETWEEN $get_par_begin AND $get_par_end";
		$query2="select count(*) from (SELECT count() FROM \"main\" WHERE (paragraph BETWEEN $get_par_begin AND $get_par_end ) group by real ) T";

		$query3="SELECT sum(length(real)) FROM \"main\" WHERE paragraph BETWEEN $get_par_begin AND $get_par_end";
		$query4="select sum(length(real)) from (SELECT count(),real FROM \"main\" WHERE (paragraph BETWEEN $get_par_begin AND $get_par_end ) group by real ) T";
	}
		$allword=PDO_FetchOne($query1);
		$allword_token=PDO_FetchOne($query2);
		$allwordLen=PDO_FetchOne($query3);
		$allword_tokenLen=PDO_FetchOne($query4);

		echo $allword.",".$allword_token.",".$allwordLen.",".$allword_tokenLen;
	

?>
