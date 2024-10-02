<?php
require_once "../config.php";
require_once "../public/load_lang.php";
require_once "../pcdl/html_head.php";
?>
	
<body class="sync_body">
	<script language="javascript" src="sync.js"></script>

<?php 
	if(!isset($_COOKIE["userid"])){
		echo "没有登录，请在登录后执行同步操作";
	}
	else if(!isset($_COOKIE["sync_userid"])){
		?>
		<h3>等在跳转到登录页面</h3>
		<script>
		window.location.assign("check_login.php");
		</script>	
		<?php
	}
	else{
?>
	<div class="fun_block" style="margin-left: auto;margin-right: auto;max-width: 100%;">
		<h2>Sync</h2>
		<div id="wiki_search" style="width:100%;">
			<div>
				<h3>Server Address</h3>
				<input id="sync_server_address" type="input" readonly value="<?php echo $_COOKIE["sync_server"];?>" style="width:30em;" />
				<h3>Local Address</h3>
				<input id="sync_local_address" type="input" placeholder="local address" value="" style="width:30em;" />
				
				<button onclick="sync_pull()">pull</button>
				<button onclick="sync_push()">push</button>
				<button onclick="sync_stop()">stop</button>
			</div>
		</div>
		<div>上次更新时间：</div>
		
		<div id="sync_result"></div>
		<div id="sync_log"></div>
	</div>
		
	<script>
	let localhost;
	let base = "/app/sync/";
	let path="";
	if(location.pathname.indexOf(base)>=0){
		path = location.pathname.slice(0,location.pathname.indexOf(base));
	}
	localhost=location.protocol +"//"+ location.hostname + location.port + path;

	$("#sync_local_address").val(localhost);
	$(document).ready(function(){
		sync_index_init();
	});

	</script>		
		<?php
	}
?>

	</body>
</html>