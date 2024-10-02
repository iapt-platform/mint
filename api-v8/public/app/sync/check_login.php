<?php
	require_once "../config.php";
	require_once "../public/load_lang.php";
	require_once "../pcdl/html_head.php";
	echo "<body>";
if(!isset($_COOKIE["sync_userid"])){
	#未登录
?>
	<script language="javascript" src="sync.js"></script>

	<h2>数据同步-登录</h2>
	<h3>远程服务器地址</h3>
	<div>
	<input id="sync_server_address" type="input"  placeholder="server address" value="https://www.wikipali.org" style="width:30em;" />
	</div>
	<h3>用户名：<?php echo $_COOKIE["username"];?></h3>
	<div>
	<input id="userid" type="hidden" name="userid"  value="<?php echo $_COOKIE["userid"];?>" style="width:30em;" />
	
	<input id="password" type="password" name="password" placeholder="password" value="" style="width:30em;" />
	<br>
	本地地址：<input id="local"  type="input" readonly name="local"  value="" style="width:30em;" />
	</div>
	<div>
	<button onclick="login()"> 登录</button>
	</div>
	<div id="server_msg">

	</div>

	<script>
	let localhost;
	let base = "/app/sync/";
	let path="";
	if(location.pathname.indexOf(base)>=0){
		path = location.pathname.slice(0,location.pathname.indexOf(base));
	}
	localhost=location.protocol +"//"+ location.hostname + location.port + path;

	$("#local").val(localhost);

	</script>
<?php
}
else{
	#已经登录
	?>
	<h3>远程服务器地址：<?php echo $_COOKIE["sync_server"]?></h3>
	<h3>用户名：<?php echo $_COOKIE["username"]?></h3>
	<?php
}
?>

</body>
</html>