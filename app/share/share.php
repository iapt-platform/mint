<?php
require_once '../studio/index_head.php';
?>

<body>
<script language="javascript" src="../share/share.js"></script>
<style>
.item_block{
	border-bottom: 1px solid var(--border-line-color);
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
#user_list li{
    border-bottom: 1px solid var(--btn-border-color);
    width: 270px;
    border-radius: 5px;
    line-height: 24px;
    padding: 0 5px;
	display:flex;
	justify-content: space-between;
}
#user_list li>.btn_del{
	visibility: hidden;
}
#user_list li:hover .btn_del{
	visibility: visible;
}
</style>
<div class=" " >
	<div class="item_block" style="display:none;">
		<h2 id="res_type"></h2>
		<div id="res_title">
		</div>
	</div>
	<div class="item_block">
		<h2><?php echo $_local->gui->privacy;?></h2>
		<ul>
			<li title="<?php echo $_local->gui->private_note;?>"><input type="radio" name="list" checked /><?php echo $_local->gui->private;?></li>
			<li title="<?php echo $_local->gui->public_note;?>"><input type="radio" name="list" /><?php echo $_local->gui->public;?></li>
		</ul>
	</div>
	<div class="item_block" style="display:none;">
		<h2>分享链接</h2>
		<div><button>创建</button></div>
		<div id="share_link">
			<button>关闭分享链接</button>
			<div style="display:flex;"><span style="max-width:200px;"><input type="input" name="" /></span><button>复制分享链接</button></div>
			<select>
					<option value="10">查看</option>
					<option value="20">编辑</option>
			</select>
		</div>
	</div>	
	<div class="item_block">
		<h2><?php echo $_local->gui->collaborate;?></h2>
		<div>
			<div style="display:flex;">
			<select id="user_type" >
				<option value="1">👤<?php echo $_local->gui->personal;?></option>
				<option value="2">👥<?php echo $_local->gui->group;?></option>
			</select>
			<span style="max-width:200px;">
			<input id="search_user" type="input" name="" placeholder="🔍<?php echo $_local->gui->username . ' or ' . $_local->gui->group;?>" onkeyup="username_search_keyup(event,this)" />
			</span>

			</div>
			<div id="user_search">
			</div>
			<div id="user_list_shell">
				<div id="user_list">
				</div>
				<div id="coop_new_tools">
					<select id="coop_new_power">
						<option value="10">👁‍🗨<?php echo $_local->gui->viewer;?></option>
						<option value="20">📝<?php echo $_local->gui->editor;?></option>
					</select>
					<button onclick="add_coop()"><?php echo $_local->gui->add;?></button>
					<button onclick="cancel_coop()"><?php echo $_local->gui->cancel;?></button>
				</div>
			</div>
		</div>
		<div>
		<h3><?php echo $_local->gui->cooperators;?></h3>
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