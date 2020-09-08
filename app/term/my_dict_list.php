<?php
require_once '../studio/index_head.php';
?>
<body id="file_list_body" onLoad="course_list()">

	<script >
	var gCurrPage="term";
	</script>

	<style>
	#term {
		background-color: var(--btn-border-color);
		
	}
	#term:hover{
		background-color: var(--btn-border-color);
		color: var(--btn-color);
		cursor:auto;
    }
    #word_list{
        width:unset;
    }
	</style>

	<?php
	require_once '../studio/index_tool_bar.php';
	?>
		
	<div class="index_inner" style="    margin-left: 18em;margin-top: 5em;">
		<div id="word_list"  class="file_list_block">

		<div class="tool_bar">
	<div>
	术语百科
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

if(isset($_GET["page"])){
    $iCurrPage=$_GET["page"];
}
else{
    $iCurrPage=0;
}
$iOnePage=300;

$dictFileName=_FILE_DB_TERM_;
PDO_Connect("sqlite:$dictFileName");

$query = "select count(*) as co  from term where owner= ".$PDO->quote($USER_NAME);

$allWord = PDO_FetchOne($query);
$iCountWords=$allWord;

if($iCountWords==0){
    echo "<div id='setting_user_dict_count'>您的术语字典中没有单词。</div>";
}
else{
    echo "<div id='setting_user_dict_count'>您的术语字典中已经有{$iCountWords}个单词。</div>";
    $iPages=ceil($iCountWords/$iOnePage);
    if($iCurrPage>$iPages){
        $iCurrPage=$iPages;
    }
    $begin=$iCurrPage*$iOnePage;

    $query = "select *  from term where owner= ".$PDO->quote($USER_NAME);
    $allWords = PDO_FetchAll($query);

    echo '<div id="setting_user_dict_nav">';

    if($iCurrPage==0){
        echo "第一页 | ";
        echo "上一页";
    }
    else{
        echo "<a href=\"setting.php?item=term&page=0\">第一页</a>";
        $prevPage=$iCurrPage-1;
        echo "<a href=\"setting.php?item=term&page={$prevPage}\">上一页</a>";
    }
    
    echo "第<span style='display:inline-block;width:4em;'><input type=\"input\" value=\"".($iCurrPage+1)."\" size=\"4\" /></span>页";
    echo "共{$iPages}页";
    
    if($iCurrPage<$iPages-1){
        echo "<a href=\"setting.php?item=term&page=".($iCurrPage+1)."\">下一页</a>";
        echo "<a href=\"setting.php?item=term&page=".($iPages-1)."\">最后一页</a>";
        
    }
    else{
        echo "下一页 | 最后一页";
    }
    ?>
    </div>
    <div>
        <div style="display:flex;">
            <div style='max-width:2em;flex:1;'></div>
            <div style='flex:1;'>序号</div>
            <div style='flex:2;'>拼写</div>
            <div style='flex:2;'>意思</div>
            <div style='flex:2;'>第二个意思</div>
            <div style='flex:5;'>注解</div>
            <div style='flex:1;'>语言</div>
        </div>
    <?php
    
    foreach($allWords as $key => $word){
        echo '<div class="file_list_row" style="padding:5px;">';
        echo '<div style="max-width:2em;flex:1;"><input type="checkbox" /></div>';
        echo "<div style='flex:1;'>".($key+1)."</div>";
        echo "<div style='flex:2;'>{$word["word"]}</div>";
        echo "<div style='flex:2;'>{$word["meaning"]}</div>";
        echo "<div style='flex:2;'>{$word["other_meaning"]}</div>";
        echo "<div style='flex:5;'><textarea style='width:100%;'>{$word["note"]}</textarea></div>";
        echo "<div style='flex:1;'></div>";
        echo "</div>";
    }

    echo '</div>';
}
    ?>

    </div>
			
	
<?php
require_once '../studio/index_foot.php';
?>