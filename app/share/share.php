<?php
require_once '../studio/index_head.php';
?>

<body>
<script language="javascript" src="../share/share.js"></script>
<style>
.item_block{
	border-top: 1px solid var(--border-line-color);
    padding: 0 0 2em 0;
}
#coop_new_tools{
	display:none;
}
select{
	background-color:unset;
}
input[type="text"], input[type="input"], input[type="password"], textarea{
	background-color:unset;
	color:unset;
}
#user_search {
    border: 1px solid var(--border-line-color);
    display: inline-block;
    position: absolute;
    background-color: var(--drop-bg-color);
}
#user_search_list li{
	padding:5px;
}
#user_search_list li:hover{
	background-color: var(--link-color);
    color: var(--tool-color);
}
.file_list_row:hover {
    background-color: var(--link-color);
}
.icon{
	fill: var(--main-color);
	height: 28px;
    width: 28px;
}
</style>
<div class=" " >
	<div class="item_block">
		<h2>隐私设置</h2>
		<ul>
			<li><input type="radio" name="list" checked />私有</li>
			<li><input type="radio" name="list" />公开列出</li>
		</ul>
	</div>
	<div class="item_block">
		<h2>分享链接</h2>
		<div><button>创建分享链接</button></div>
		<div id="share_link">
			<button>关闭分享链接</button>
			<div style="display:flex;"><span style="max-width:200px;"><input type="input" name="" /></span><button>复制分享链接</button></div>
			<select>
					<option value="10">查看者</option>
					<option value="20">编辑者</option>
			</select>
		</div>
	</div>	
	<div class="item_block">
		<h2>协作</h2>
		<div>
			<div style="display:flex;">
			<span>
				<svg class="icon">
					<use xlink:href="../studio/svg/icon.svg#ic_add_person"></use>
				</svg>
			</span>
			<span style="max-width:200px;">
			<input id="search_user" type="input" name="" placeholder="输入用户名或组名" onkeyup="username_search_keyup(event,this)" />
			</span>
			<select id="user_type" >
				<option value="1">用户</option>
				<option value="2">工作组</option>
			</select>
			</div>
			<div id="user_search">
			</div>
			<div id="user_list_shell">
				<div id="user_list">
				</div>
				<div id="coop_new_tools">
					<select id="coop_new_power">
						<option value="10">查看者</option>
						<option value="20">编辑者</option>
					</select>
					<button onclick="add_coop()">添加</button>
					<button onclick="cancel_coop()">取消</button>
				</div>
			</div>
		</div>
		<div>
		<h3>有权使用的人</h3>
		<div id="coop_list">
		</div>
		</div>
	</div>	
</div>
<script>
$(document).ready(function(){
	<?php
	if(isset($_GET["id"]) && isset($_GET["type"])){
		echo "_res_id = '{$_GET["id"]}'; \n";
		echo "_res_type = '{$_GET["type"]}'; \n";
		echo "share_load('{$_GET["id"]}','{$_GET["type"]}');";
	}
	?>
});
</script>
</body>
</html>