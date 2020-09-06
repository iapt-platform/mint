<style>
.file_list_block{
    width:95%;
}
</style>
<div class="tool_bar">

<?php
require_once "../path.php";
require_once "../public/_pdo.php";
require_once '../ucenter/function.php';

global $PDO;
PDO_Connect("sqlite:"._FILE_DB_COURSE_);
$query = "select * from course where id = '{$_GET["course"]}'   limit 0,1";
$Fetch = PDO_FetchAll($query);
if(count($Fetch)==0)
{
    echo "无法找到此课程。可能该课程已经被拥有者删除。";
    exit;
}
$course_info = $Fetch[0];

echo '<div>';
echo '<a href="../course/my_course_index.php">全部课程</a> > '.$course_info["title"];
echo '</div>';

?>

    <div>
		<span class="icon_btn_div">
			<span class="icon_btn_tip"><?php echo $_local->gui->new;?></span>
			<button id="file_share" type="button" class="icon_btn"  title=" ">
			<a href="../course/my_course_index.php?course=<?php echo $course_info["id"] ?>&op=newlesson">
				<svg class="icon">
					<use xlink:href="../studio/svg/icon.svg#ic_add_circle"></use>
				</svg>
				</a>
			</button>
		</span>
    </div>
</div>

<div style="display: flex; color: var(--border-line-color);border-bottom: 1px solid var(--tool-line-color);padding: 0 18px 18px 18px;">

<?php
    echo '<div><div>';
    echo "<span style='padding-right:5px;'>开课：".strftime("%b-%d-%Y",$course_info["create_time"]/1000)."</span>";
    echo "<span style='padding:5px;'>最新：".strftime("%b-%d-%Y",$course_info["modify_time"]/1000)."</span>";
    echo "<span style='padding:5px;'>视频数量：{$course_info["lesson_num"]}</span>";
    echo "<span style='padding:5px;'>播放：100</span>";
    echo "<span style='padding:5px;'>点赞：10</span>";
    echo "<span style='padding:5px;'>订阅：20</span>";
    echo '</div>';

    echo '<div style="padding-margin:5px; display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 2;
    overflow: hidden;">';
    echo '简介：'.$course_info["summary"];
    echo '</div>';
    echo '<div>';
    echo '教师：'.ucenter_getA($course_info["teacher"]);
    echo '</div></div>';
?>
<div><a href="../course/my_course_index.php?course=<?php echo $course_info["id"];?>&op=edit">
<svg viewBox="0 0 24 24" id="ic_mode_edit" height="24" width="24">
        <path fill="silver" d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04a.996.996 0 0 0 0-1.41l-2.34-2.34a.996.996 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"></path>
</svg>
</a></div>
</div>

<div style="display:flex;">

<div style="flex:8;padding:0 0.8em;">

<?php
$query = "select * from lesson where course_id = '{$_GET["course"]}'   limit 0,100";
$fAllLesson = PDO_FetchAll($query);


echo '<div id="userfilelist">';

foreach($fAllLesson as $row){

    echo '<div class="file_list_row" style="display:flex;">';

    echo '<div style="width:2em;">';

    echo '</div>';

    echo '<div style="flex:7;padding: 5px 15px;">';

    echo '<div class="pd-10">';
    echo '<div  style="padding-bottom:5px;font-size: 120%;"><a href="../course/my_course_index.php?lesson='.$row["id"].'">'.$row["title"].'</a></div>';
    echo '<div class="summary"  style="padding-bottom:5px;">'.$row["subtitle"].'</div>';
    echo '<div class="summary"  style="padding-margin:5px; display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 2;
    overflow: hidden;">'.$row["summary"].'</div>';
    echo '<div class="author"  style="padding-bottom:5px;">主讲：'.ucenter_getA($row["teacher"]).'</div>';
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
        <div>创建时间：
        <?php
            echo strftime("%b-%d-%Y",$course_info["create_time"]/1000);
        ?>
        </div>
        <div>修改时间：
        <?php
            echo strftime("%b-%d-%Y",$course_info["modify_time"]/1000);
        ?>
        </div>
        <div>点击：</div>
        <div>点赞：</div>
        <div>收藏：</div>

    </div>

</div>