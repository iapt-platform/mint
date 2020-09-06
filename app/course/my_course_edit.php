<style>
.file_list_block{
    width:90%;
}
</style>

<div class="tool_bar">

<?php
require_once "../path.php";
require_once "../public/_pdo.php";
require_once '../media/function.php';

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
echo '<a href="../course/my_course_index.php?course='.$course_info["id"].'">'.$course_info["title"].'</a> > > 修改';
echo '</div>';

echo '<div></div>';
echo '</div>';


$coverList = array();

$coverList[] = $course_info["cover"];

$covers = media_get($coverList);
foreach ($covers as $value) {
    $cover["{$value["id"]}"] = $value["link"];
}


    $coverlink = $cover["{$lesson_info["cover"]}"];
    if(substr($coverlink,0,6)=="media:"){
        $cover_html = '<div style="width: 20em;"><img src="'._DIR_USER_IMG_LINK_.'/'.substr($coverlink,6).'" width="100%" height="auto"></div>';
    }
    else{
        $cover_html =  '<div style="width: 20em;"><img src="'.$coverlink.'" width="50" height="50"></div>';
    }

echo '<div style="display:flex;">';

echo '<div style="flex:8;padding:0 0.8em;">';
echo '<form action="../course/my_course_index.php" onsubmit="return course_validate_form(this)"  method="POST">';
echo '<input type="hidden" name="course" value="'.$course_info["id"].'" />';
echo '<input type="hidden" name="op" value="update" />';
echo '<div id="userfilelist">';


    echo '<div style="display:flex;">';
    echo '<div style="flex:2;">'.'老师'.'</div>';
    echo '<div id="teacher_id" style="flex:8;"></div>';
    echo '<input id="form_teacher" type="hidden" name="teacher" value="'.$course_info["teacher"].'" />';
    echo '</div>'; 

    echo '<div style="display:flex;">';
    echo '<div style="flex:2;">'.'语言'.'</div>';
    echo '<div id="teacher_id" style="flex:8;">';
    echo '<input  type="input" name="lang" value="'.$course_info["lang"].'" />';
    echo '</div>';
    echo '</div>'; 

    echo '<div style="display:flex;">';
    echo '<div style="flex:2;">'.'标题'.'</div>';
    echo '<div style="flex:8;">';
    echo '<input type="input" id="form_title"  name = "title" value="'.$course_info["title"].'" />';
    echo '<span id = "error_form_title" ></span>';
    echo '</div>';

    echo '</div>';

    echo '<div style="display:flex;">';
    echo '<div style="flex:2;">'.'副标题'.'</div>';
    echo '<div style="flex:8;"><input type="input" name = "subtitle" value="'.$course_info["subtitle"].'" /></div>';
    echo '</div>';

    echo '<div style="display:flex;">';
    echo '<div style="flex:2;">'.'简介'.'</div>';
    echo '<div style="flex:8;"><textarea name="summary" style="height:6em;">'.$course_info["summary"].'</textarea></div>';
    echo '</div>';

    echo '<div style="display:flex;">';
    echo '<div style="flex:2;">'.'标签'.'</div>';
    echo '<div style="flex:8;"><input type="input" name = "tag" value="'.$course_info["tag"].'" /></div>';
    echo '</div>';

    echo '<div style="display:flex;">';
    echo '<div style="flex:2;">'.'附件链接'.'</div>';
    echo '<div style="flex:8;"><input type="input" name = "attachment" value="'.$course_info["attachment"].'" /></div>';
    echo '</div>';

echo '</div>';
?>

<input type="submit" />
</form>
</div>

<div style="flex:2;border-left: 1px solid var(--tool-line-color);padding-left: 12px;">
<div style="width:100%;padding:4px;">
<?php
    echo $lesson_info["link"];
?>
</div>
<div>创建时间：
<?php
    echo strftime("%b-%d-%Y",$lesson_info["create_time"]/1000);
?>
</div>
<div>修改时间：
<?php
    echo strftime("%b-%d-%Y",$lesson_info["modify_time"]/1000);
?>
</div>
<div>点击：</div>
<div>点赞：</div>
<div>收藏：</div>

</div>

</div>

<script>
    name_selector_init("teacher_id",{input_id:"form_teacher"});
</script>