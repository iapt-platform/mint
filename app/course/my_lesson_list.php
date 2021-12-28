<?php
require_once '../studio/index_head.php';
?>
<body id="file_list_body" onLoad="course_list()">

	<script language="javascript" src="../course/my_couse.js"></script>
	<script language="javascript" src="../ucenter/name_selector.js"></script>
	<script >
	var gCurrPage="course";
	</script>

	<style>
	#course {
		background-color: var(--btn-border-color);
		
	}
	#course:hover{
		background-color: var(--btn-border-color);
		color: var(--btn-color);
		cursor:auto;
	}
	</style>

	<?php
	require_once '../studio/index_tool_bar.php';
	?>
		
	<div class="index_inner" style="    margin-left: 18em;margin-top: 5em;">
		<div id="course_list"  class="file_list_block">


<style>
.file_list_block{
    width:95%;
}
</style>
<div class="tool_bar">

<?php
require_once "../config.php";
require_once "../public/_pdo.php";
require_once '../ucenter/function.php';

global $PDO;
PDO_Connect(""._FILE_DB_COURSE_);
$query = "SELECT * from course where id = ?   limit 0,1";
$Fetch = PDO_FetchAll($query,array($_GET["course"]));
if(count($Fetch)==0)
{
    echo "无法找到此课程。可能该课程已经被拥有者删除。";
    exit;
}
$course_info = $Fetch[0];

echo '<div>';
echo '<a href="../course/my_course_index.php">'.$_local->gui->all_courses.'</a> > '.$course_info["title"];
echo '</div>';

?>

    <div>
		<span class="icon_btn_div">
			<span class="icon_btn_tip"><?php echo $_local->gui->new_lesson;?></span>
			<button id="file_share" type="button" class="icon_btn"  title=" ">
			<a href="../course/my_lesson_new.php?course=<?php echo $course_info["id"] ?>&op=newlesson">
                <svg style="" t="1601520759574" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="7839" width="200" height="200">
                    <path d="M192 256h640v320H192z" p-id="7840"></path>
                    <path d="M832 192.5v447.29H192V192.5h640m0-64H192c-35.35 0-64 28.65-64 64v447.29c0 35.35 28.65 64 64 64h640c35.35 0 64-28.65 64-64V192.5c0-35.35-28.65-64-64-64z" p-id="7841">
                    </path><path d="M384.09 659.34h63.81v226.72h-63.81z" p-id="7842"></path>
                    <path d="M447.78 659.46v226.47h-63.56V659.46h63.56m0.25-0.25h-64.06v226.97h64.06V659.21z" p-id="7843"></path><path d="M576.09 659.34h63.81v226.72h-63.81z" p-id="7844"></path>
                    <path d="M639.78 659.46v226.47h-63.56V659.46h63.56m0.25-0.25h-64.06v226.97h64.06V659.21z" p-id="7845"></path>
                    <path d="M288.03 895.94c-17.59 0-31.91-14.31-31.91-31.91s14.31-31.91 31.91-31.91h447.94c17.59 0 31.91 14.31 31.91 31.91s-14.31 31.91-31.91 31.91H288.03z" p-id="7846"></path>
                    <path d="M735.97 832.25c17.52 0 31.78 14.26 31.78 31.78s-14.26 31.78-31.78 31.78H288.03c-17.52 0-31.78-14.26-31.78-31.78s14.26-31.78 31.78-31.78h447.94m0-0.25H288.03c-17.69 0-32.03 14.34-32.03 32.03s14.34 32.03 32.03 32.03h447.94c17.69 0 32.03-14.34 32.03-32.03S753.66 832 735.97 832z" p-id="7847">
                    </path><path style="fill:#545454" d="M384 384h256v64H384z" p-id="7848"></path>
                    <path style="fill:#545454" d="M480 288h64v256h-64z" p-id="7849"></path>
                </svg>
			</a>
			</button>
		</span>
    </div>
</div>

<div style="display: flex; color: var(--border-line-color);border-bottom: 1px solid var(--tool-line-color);padding: 0 18px 18px 18px;">

<?php
    echo '<div><div>';
    echo "<span style='padding-right:5px;'>".$_local->gui->course_begin."：".strftime("%b-%d-%Y",$course_info["create_time"]/1000)."</span>";
    echo "<span style='padding:5px;'>".$_local->gui->recent_update."：".strftime("%b-%d-%Y",$course_info["modify_time"]/1000)."</span>";
    echo "<span style='padding:5px;'>".$_local->gui->num_of_lesson."：{$course_info["lesson_num"]}</span>";
    echo "<span style='padding:5px;'>".$_local->gui->num_of_played."：100</span>";
    echo "<span style='padding:5px;'>".'<svg t="1600445373282" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="2368" width="32" height="32"><path fill="silver" d="M854.00064 412.66688h-275.99872v-35.99872c48-102.00064 35.99872-227.99872 0-288-12.00128-18.00192-35.99872-35.99872-54.00064-35.99872s-35.99872 6.00064-35.99872 54.00064c0 96-6.00064 137.99936-24.00256 179.99872-12.00128 29.99808-77.99808 96-156.00128 120.00256v480c12.00128 6.00064 35.99872 24.00256 54.00064 29.99808 18.00192 12.00128 48 18.00192 60.00128 18.00192h306.00192c77.99808 0 108.00128-29.99808 108.00128-66.00192 0-18.00192 0-29.99808-18.00192-35.99872V796.672c41.99936 0 83.99872-12.00128 83.99872-48 0-29.99808-12.00128-35.99872-18.00192-35.99872v-35.99872h6.00064c24.00256 0 60.00128-35.99872 60.00128-60.00128 0-18.00192-6.00064-35.99872-18.00192-41.99936-6.00064-6.00064-24.00256-6.00064-24.00256-6.00064v-35.99872s12.00128 0 24.00256-12.00128c18.00192-12.00128 18.00192-42.00448 18.00192-42.00448v-12.00128c0-29.99808-48-54.00064-96-54.00064zM67.99872 478.6688l35.99872 408.00256c6.00064 24.00256 24.00256 48 48 48h83.99872c6.00064 0 12.00128-6.00064 18.00192-12.00128s12.00128-6.00064 18.00192-12.00128V412.66688H128c-35.99872 0-60.00128 35.99872-60.00128 66.00192z" p-id="2369"></path></svg>';
    echo "10"."</span>";
    echo "<span style='padding:5px;'>".$_local->gui->signed_up."：20</span>";
    echo '</div>';

    echo '<div style="padding-margin:5px; display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 2;
    overflow: hidden;">';
    echo $_local->gui->introduction.'：'.$course_info["summary"];
    echo '</div>';
    echo '<div>';
    echo $_local->gui->speaker.'：'.ucenter_getA($course_info["teacher"]);
    echo '</div></div>';
?>
<div><a href="../course/my_course_edit.php?course=<?php echo $course_info["id"];?>">
<svg viewBox="0 0 24 24" id="ic_mode_edit" height="24" width="24">
        <path fill="silver" d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04a.996.996 0 0 0 0-1.41l-2.34-2.34a.996.996 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"></path>
</svg>
</a></div>
</div>

<div style="display:flex;">

<div style="flex:8;padding:0 0.8em;">

<?php
$query = "SELECT * from lesson where course_id = ?   limit 0,100";
$fAllLesson = PDO_FetchAll($query,array($_GET["course"]));


echo '<div id="userfilelist">';

foreach($fAllLesson as $row){

    echo '<div class="file_list_row" style="display:flex;">';

    echo '<div style="width:2em;">';

    echo '</div>';

    echo '<div style="flex:7;padding: 5px 15px;">';

    echo '<div class="pd-10">';
    echo '<div  style="padding-bottom:5px;font-size: 120%;"><a href="../course/my_lesson_edit.php?lesson='.$row["id"].'">'.$row["title"].'</a></div>';
    echo '<div class="summary"  style="padding-bottom:5px;">'.$row["subtitle"].'</div>';
    echo '<div class="summary"  style="padding-margin:5px; display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 2;
    overflow: hidden;">'.$row["summary"].'</div>';
    echo '<div class="author"  style="padding-bottom:5px;">'.$_local->gui->speaker.'：'.ucenter_getA($row["teacher"]).'</div>';
    echo '</div>';    

    echo '</div>';
    echo '</div>';
}
echo '</div>';
?>

    </div>

    <div style="display:none;flex:2;border-left: 1px solid var(--tool-line-color);padding-left: 12px;">
        <div><a href="../course/my_course_edit.php?course=<?php echo $course_info["create_time"];?>&op=edit">[修改课程信息]</a></div>
        <div style="width:100%;padding:4px;">
        <?php
            echo $course_info["cover"];
        ?>
        </div>
        <div><?php echo $_local->gui->created_time ?>：
        <?php
            echo strftime("%b-%d-%Y",$course_info["create_time"]/1000);
        ?>
        </div>
        <div><?php echo $_local->gui->modified_time ?>：
        <?php
            echo strftime("%b-%d-%Y",$course_info["modify_time"]/1000);
        ?>
        </div>
        <div><?php echo $_local->gui->num_of_hit ?>：</div>
        <div><svg t="1600445373282" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="2368" width="32" height="32"><path fill="silver" d="M854.00064 412.66688h-275.99872v-35.99872c48-102.00064 35.99872-227.99872 0-288-12.00128-18.00192-35.99872-35.99872-54.00064-35.99872s-35.99872 6.00064-35.99872 54.00064c0 96-6.00064 137.99936-24.00256 179.99872-12.00128 29.99808-77.99808 96-156.00128 120.00256v480c12.00128 6.00064 35.99872 24.00256 54.00064 29.99808 18.00192 12.00128 48 18.00192 60.00128 18.00192h306.00192c77.99808 0 108.00128-29.99808 108.00128-66.00192 0-18.00192 0-29.99808-18.00192-35.99872V796.672c41.99936 0 83.99872-12.00128 83.99872-48 0-29.99808-12.00128-35.99872-18.00192-35.99872v-35.99872h6.00064c24.00256 0 60.00128-35.99872 60.00128-60.00128 0-18.00192-6.00064-35.99872-18.00192-41.99936-6.00064-6.00064-24.00256-6.00064-24.00256-6.00064v-35.99872s12.00128 0 24.00256-12.00128c18.00192-12.00128 18.00192-42.00448 18.00192-42.00448v-12.00128c0-29.99808-48-54.00064-96-54.00064zM67.99872 478.6688l35.99872 408.00256c6.00064 24.00256 24.00256 48 48 48h83.99872c6.00064 0 12.00128-6.00064 18.00192-12.00128s12.00128-6.00064 18.00192-12.00128V412.66688H128c-35.99872 0-60.00128 35.99872-60.00128 66.00192z" p-id="2369"></path></svg></div>
        <div><?php echo $_local->gui->favorite ?>：</div>

    </div>

</div>


</div>
		
        </div>
        
    <?php
    require_once '../studio/index_foot.php';
    ?>