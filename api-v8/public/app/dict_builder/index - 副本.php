<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<script src="../public/js/jquery.js"></script>
	<script src="js/dict.js"></script>
	<title>PCD Studio</title>
	<style>
	#body-inner{
		display:flex;
	}
	#left-ref-dict{
		
	}
	#build-dict{
		flex:7;
	}
	.one_mean{
		display:inline-block;
		background-color:#EEE;
		padding:4px;
		margin:2px;
	}
	textarea{
		width:100%;
		height:1em;
	}
	#right_tool_bar{
		flex:3;
		position:fix;
		top:0;
		left:0;
		height:100%
	}
	</style>

	<script>
	function mean_keyup(obj,id){
		var newmean=obj.value;
		$("#mean_list_"+id).html(render_mean_list(newmean));
	}
	function render_mean_list(strMean){
		var output="";
		var arrMean=strMean.split("$");
		for(var i in arrMean){
			output+="<div class='one_mean'>"+arrMean[i]+"<span>X</span></div>";
		}
		return(output);
	}	
	</script>
</head>
<body class="mainbody" id="mbody" >
	<div id="body-inner">

			<div id="right_tool_bar" >
				<div id="right_tool_bar_title">
					<div id="dict_ref_search_input_div">
						<div id="dict_ref_search_input_head">
							<input id="dict_ref_search_input" type="input" onkeyup="dict_input_keyup(event,this)">
						</div>
						<div><span id="input_parts"><span></span></span></div>
					</div>
				</div>
				<div id="right_tool_bar_inner">
					<div id="dict_ref_search">
						<div id="dict_ref_search_result">
						</div>
					</div>
				</div>
			</div>

		
		<div id="build-dict">
<?php

include "./config.php";
include "./_pdo.php";


global $PDO;
$dictFileName=$dir_dict_3rd."bhmf.db";
PDO_Connect("$dictFileName");
		$query = "SELECT * from dict where 1  limit 0,1000";
		$Fetch = PDO_FetchAll($query);
		$iFetch=count($Fetch);
		if($iFetch>0){
			for($i=0;$i<$iFetch;$i++){
				echo  "<div>";
				$type=$Fetch[$i]["type"];
				$gramma=$Fetch[$i]["gramma"];
				$word=$Fetch[$i]["pali"];
				$mean=$Fetch[$i]["mean"];
				$note=$Fetch[$i]["detail"];
				$id=$Fetch[$i]["id"];
				$arrMean=str_getcsv($mean,"$");
				echo "<h3>$word</h3>";
				echo "Type:<input type='text' value='$type' />";
				echo "Gramma:<input type='text' value='$gramma' />";
				echo "<div id='mean_list_$id'>";
				foreach($arrMean as $oneMean){
					echo "<div class='one_mean'>".$oneMean."</div>";
				}
				echo "</div>";
				echo "<div><textarea row=2 col=100 onkeyup=\"mean_keyup(this,$id)\">$mean</textarea></div>";
				echo "<div><textarea row=2 col=100 >$note</textarea></div>";
				echo "</div>";

			}
		}

?>		
		</div>

	</div>
</body>
</html>