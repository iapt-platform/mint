<?php
require_once '../studio/index_head.php';
?>
<body id="file_list_body" onLoad="course_list()">

	<script language="javascript" src="../course/my_couse.js"></script>
	<script language="javascript" src="../ucenter/name_selector.js"></script>
	<script >
	var gCurrPage="channal";
	</script>

	<style>
	#channal {
		background-color: var(--btn-border-color);
		
	}
	#channal:hover{
		background-color: var(--btn-border-color);
		color: var(--btn-color);
		cursor:auto;
	}
	</style>

	<?php
	require_once '../studio/index_tool_bar.php';
	?>
		
	<div class="index_inner" style="    margin-left: 18em;margin-top: 5em;">
		<div id="channal_list"  class="file_list_block">

		<div class="tool_bar">
	<div>
	频道
	</div>

	<div>
		<span class="icon_btn_div">
			<span class="icon_btn_tip"><?php echo $_local->gui->add;?></span>
			<button id="file_add" type="button" class="icon_btn" title=" ">
				<a href="../course/my_channal_new.php">
				<svg class="icon">
					<use xlink:href="../studio/svg/icon.svg#ic_add_circle"></use>
				</svg>
				</a>
			</button>
		</span>
		
		<span class="icon_btn_div">				
			<span class="icon_btn_tip"><?php echo $_local->gui->recycle_bin;?></span>
			<button id="to_recycle" type="button" class="icon_btn" onclick="file_del()" title=" ">
				<svg class="icon">
					<use xlink:href="../studio/svg/icon.svg#ic_delete"></use>
				</svg>
			</button>
		</span>	
	</div>
				
</div>

<div id="userfilelist">
<?php
//

require_once "../path.php";
require_once "../public/_pdo.php";
require_once '../public/load_lang.php';
require_once '../ucenter/function.php';

global $PDO;
PDO_Connect("sqlite:"._FILE_DB_CHANNAL_);
$query = "SELECT * from channal where owner = '{$_COOKIE["userid"]}'   limit 0,100";
$Fetch = PDO_FetchAll($query);

foreach($Fetch as $row){
    echo '<div class="file_list_row" style="padding:5px;">';

    echo '<div class="pd-10"  style="width:2em;"><input  type="checkbox" /></div>';
    echo '<div class="title" style="flex:3;padding-bottom:5px;"><a href="../course/my_course_index.php?course='.$row["id"].'">'.$row["name"].'</a></div>';
    echo '<div class="summary"  style="flex:4;padding-bottom:5px;">'.$row["summary"].'</div>';
    echo '<div class="summary"  style="flex:1;padding-bottom:5px;">'.$row["status"].'</div>';
    echo '<div class="author"  style="flex:1;padding-bottom:5px;">'.$row["create_time"].'</div>';
    
    echo '</div>';
}

?>				
</div>
			
		</div>
		
	</div>
	
<?php
require_once '../studio/index_foot.php';
?>

