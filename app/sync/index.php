<?php

require_once "../path.php";

?>

<!DOCTYPE html >
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>PCD Sync</title>

		<script src="../public/js/jquery.js"></script>
		<script src="../public/js/comm.js"></script>
		<script src="sync.js"></script>
	</head>	
	
	
	<body class="sync_body" onload="sync_index_init()">
		<div class="fun_block">
			<h2>Sync</h2>
			<div id="wiki_search" style="width:100%;">
				<div>
					<h3>Server Address</h3>
					<input id="sync_server_address" type="input" placeholder="server address" value="https://www.wikipali.org" style="width:30em;" />
					<h3>Local Address</h3>
					<input id="sync_local_address" type="input" placeholder="local address" value="" style="width:30em;" />
					
					<button onclick="sync_pull()">pull</button>
					<button onclick="sync_push()">push</button>
				</div>
			</div>
			<div>上次更新时间：</div>
			<div id="sync_result">

			</div>
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
</script>
	</body>
</html>