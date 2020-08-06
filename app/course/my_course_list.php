<div class="tool_bar">
	<div>
	课程
	</div>

	<div>
		<span class="icon_btn_div">
			<span class="icon_btn_tip"><?php echo $_local->gui->add;?></span>
			<button id="file_add" type="button" class="icon_btn" title=" ">
				<a href="../course/my_course_index.php?op=new">
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
require_once '../media/function.php';

global $PDO;
PDO_Connect("sqlite:"._FILE_DB_COURSE_);
$query = "select * from course where creator = '{$_COOKIE["userid"]}'  order by modify_time DESC limit 0,100";
$Fetch = PDO_FetchAll($query);

$coverList = array();
foreach($Fetch as $row){
    $coverList[] = $row["cover"];
}
$covers = media_get($coverList);
foreach ($covers as $value) {
    $cover["{$value["id"]}"] = $value["link"];
}
foreach($Fetch as $row){
    echo '<div class="file_list_row">';

    
    $coverlink = $cover["{$row["cover"]}"];
    echo '<div class="v-cover" style="flex:2;">';
    if(substr($coverlink,0,6)=="media:"){
        echo '<img src="'._DIR_USER_IMG_LINK_.'/'.substr($coverlink,6).'" width="100%" height="auto">';
    }
    else{
        echo '<img src="'.$coverlink.'" width="50" height="50">';
    }
    echo '</div>';

    echo '<div class="pd-10" style="flex:8;padding:5px;">';
    echo '<div class="title" style="padding-bottom:5px;"><a href="../course/my_course_index.php?course='.$row["id"].'">'.$row["title"].'</a></div>';
    echo '<div class="summary"  style="padding-bottom:5px;">'.$row["subtitle"].'</div>';
    echo '<div class="summary"  style="padding-bottom:5px;">'.$row["summary"].'</div>';
    echo '<div class="author"  style="padding-bottom:5px;">主讲：'.$row["teacher"].'</div>';
    echo '</div>';
    

    echo '</div>';
}

?>				
</div>