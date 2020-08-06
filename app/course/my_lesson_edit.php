<script language="javascript" src="../media/img_dlg.js"></script>
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

$query = "select * from lesson where id = '{$_GET["lesson"]}'   limit 0,1";
$Fetch = PDO_FetchAll($query);
if(count($Fetch)==0)
{
    echo "无法找到此课程。可能该课程已经被拥有者删除。";
    exit;
}
$lesson_info = $Fetch[0];

$query = "select * from course where id = '{$lesson_info["course_id"]}'   limit 0,1";
$Fetch = PDO_FetchAll($query);
if(count($Fetch)==0)
{
    echo "无法找到此课程。可能该课程已经被拥有者删除。";
    exit;
}
$course_info = $Fetch[0];



echo '<div>';
echo '<a href="../course/my_course_index.php?course='.$course_info["id"].'">'.$course_info["title"].'</a> > '.$lesson_info["title"];
echo '</div>';

echo '<div></div>';
echo '</div>';


$coverList = array();

$coverList[] = $lesson_info["cover"];

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

echo '<form action="../course/my_course_index.php" onsubmit="return lesson_validate_form(this)"  method="POST">';
echo '<input type="hidden" name="lesson" value="'.$lesson_info["id"].'" />';
echo '<input type="hidden" name="op" value="update" />';
echo '<div id="userfilelist">';
    echo '<div style="display:flex;">';
    echo '<div style="flex:2;">'.'封面'.'</div>';
    echo '<div id="img_cover" style="flex:8;">'.$cover_html.'</div>';
    echo "<input id = 'cover_id' type='hidden' name = 'cover' value='{$lesson_info["cover"]}'>";
    echo '</div>';

    echo '<div style="display:flex;">';
    echo '<div style="flex:2;">'.'标题'.'</div>';
    echo '<div style="flex:8;">';
    echo '<input type="input" id="form_title"  name = "title" value="'.$lesson_info["title"].'" />';
    echo '<span id = "error_form_title" ></span>';
    echo '</div>';

    echo '</div>';

    echo '<div style="display:flex;">';
    echo '<div style="flex:2;">'.'副标题'.'</div>';
    echo '<div style="flex:8;"><input type="input" name = "subtitle" value="'.$lesson_info["subtitle"].'" /></div>';
    echo '</div>';

    echo '<div style="display:flex;">';
    echo '<div style="flex:2;">'.'链接'.'</div>';
    echo '<div style="flex:8;"><textarea name="link" style="height:6em;">'.$lesson_info["link"].'</textarea></div>';
    echo '</div>';
    echo '<div style="display:flex;">';
    echo '<div style="flex:2;">'.'链接(中国大陆)'.'</div>';
    echo '<div style="flex:8;"><textarea name="link1" style="height:6em;">'.$lesson_info["link"].'</textarea></div>';
    echo '</div>';

    echo '<div style="display:flex;">';
    echo '<div style="flex:2;">'.'简介'.'</div>';
    echo '<div style="flex:8;"><textarea name="summary" style="height:6em;">'.$lesson_info["summary"].'</textarea></div>';
    echo '</div>';

    echo '<div style="display:flex;">';
    echo '<div style="flex:2;">'.'老师'.'</div>';
    echo '<div id="teacher_id" style="flex:8;"></div>';
    echo '<input id="form_teacher" type="hidden" name="teacher" value="'.$lesson_info["teacher"].'" />';
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
    img_dlg_init("img_cover",{input_id:"cover_id"});
</script>