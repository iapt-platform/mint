<?PHP
require_once "../pcdl/html_head.php";
require_once "function.php";
?>

<body>
<style>
.data_row{
	display:flex;
}
.key{
	flex:2;
}
.type{
	flex:1;
}
.valid{
	flex:1;
}
.rebuild{
	flex:1;
}
.count{
	flex:1;
}
.description{
	flex:8;
}
.function{
	flex:2;
}
</style>
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
	echo "<div class='data_row' >";
	echo "<div class='key'>key</div>";
	echo "<div  class='type'>type</div>";
	echo "<div  class='valid'>valid</div>";
	echo "<div  class='rebuild'>rebuild</div>";
	echo "<div class='count'>count</div>";
	echo "<div class='description'>description</div>";
	echo "<div class='function'>function</div>";
	echo "</div>";
	
	foreach ($redisList as $key => $value) {
		# code..
		echo "<div class='data_row' ";
		if($value["valid"]==false){
			echo "background-color:lightpink;";
		}
		echo "'>";
		echo "<div class='key'><b>{$value["key"]}</b></div>";
		echo "<div class='type'>{$value["type"]}</div>";
		echo "<div class='valid'>";
		if($value["valid"]){
			echo "有效";
		}
		else{
			echo "<span style='color:red;'>已经过时</span>";
		}
		echo "</div>";
		echo "<div class='rebuild'>";
		if(isset($value["rebuild"]) && !empty($value["rebuild"])){
			echo "<a href='{$value["rebuild"]}' target='_blank'>rebuild</a>";
		}
		echo "</div>";

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
		echo "<div class='count'>";
		if(isset($keys)){
			echo count($keys);
		}
		else{
			echo "未知";
		}
		echo "</div>";
		echo "<div class='description' >{$value["description"]}</div>";
		echo "<div class='funcion'><button onclick=\"del('{$value["key"]}')\">删除数据</button></div>";
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