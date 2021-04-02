<?PHP
require_once "../pcdl/html_head.php";
require_once "function.php";
?>

<body>
<?php
$redis = redis_connect();
if($redis==false){
	echo "redis 连接失败";
}
else{
	$count = $redis->dbSize();
	echo "Redis has $count keys<br>";

	if(file_exists("redis.json")){
	$redisList=json_decode(file_get_contents("redis.json"),true);
	echo "<div>";
	
	foreach ($redisList as $key => $value) {
		# code..
		echo "<div class='card' style='display:block;";
		if($value["valid"]==false){
			echo "background-color:lightpink;";
		}
		echo "'>";
		echo "<div>key:<b>{$value["key"]}</b></div>";
		echo "<div>type:{$value["type"]}</div>";
		echo "<div >valid:";
		if($value["valid"]){
			echo "有效";
		}
		else{
			echo "已经过时";
		}
		echo "</div>";
		if(isset($value["rebuild"]) && !empty($value["rebuild"])){
			echo "<div >rebuild:<a href='{$value["rebuild"]}' target='_blank'>{$value["rebuild"]}</a></div>";
		}

		if(substr($value["key"],-1)==="*"){
			$keys = $redis->keys($value["key"]);
		}
		else{
			switch ($value["type"]) {
				case 'hash':
					# code...
					$keys = $redis->hKeys($value["key"]);
					break;
				default:
					# code...
					break;
			}
		}
		echo "<div >count:";
		if(isset($keys)){
			echo count($keys);
		}
		else{
			echo "未知";
		}
		echo "</div>";
		echo "<div >description:{$value["description"]}</div>";
		echo "<div ><button onclick=\"del('{$value["key"]}')\">删除数据</button></div>";
		
		echo "</div>";
	}
	echo "</div>";

}
else{
	echo "no config file";
}
}

?>
<script>
function del(key){
	$.get("del.php",
	{
		key:key
	},
	function(data){
		alert(data);
	});
}
</script>
<?php
include "../pcdl/html_foot.php";
?>