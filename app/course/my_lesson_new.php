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
echo '<a href="../course/my_course_index.php?course='.$course_info["id"].'">'.$course_info["title"]."</a> > 新建课";
echo '</div>';

echo '<div></div>';
echo '</div>';

?>

<form action="../course/my_course_index.php" method="POST">
<input type="hidden" name="course" value="<?php echo $_GET["course"];?>" />
<input type="hidden" name="op" value="insert" />
<input type="hidden" name="course_id" value="<?php echo $_GET["course"];?>" />
<input type="hidden" name="video" value="" />

<div id="userfilelist">

    <div style="display:flex;">
    <div style="flex:2;">标题</div>
    <div style="flex:8;">
    <div style="text-align: right;color:gray;">0/32</div>
    <input type="input" name="title" value="" placeholder="标题" />
    </div>
    </div>

    <div style="display:none;">
    <div style="flex:2;">副标题</div>
    <div style="flex:8;">
        <div style="text-align: right;color:gray;">0/32</div>
        <input type="input" name="subtitle" value="" placeholder="副标题" />
    </div>
    </div>

    <div style="display:flex;">
    <div style="flex:2;">简介</div>
    <div style="flex:8;"><textarea name="summary" style="height:6em;"></textarea></div>
    </div>

    <div style="display:flex;">
    <div style="flex:2;">老师</div>
    <div   style="flex:8;">
        <div id="teacher_id"><?php echo ucenter_getA($course_info["teacher"]); ?></div>
        <input id="form_teacher" type="hidden" name="teacher" value="<?php echo $course_info["teacher"] ?>" />
    </div>
    </div> 

    <div style="display:flex;">
        <div style="flex:2;">直播信息</div>
        <div style="flex:8;"><textarea  name="live"  style="height:6em;"></textarea></div>
    </div>
    <div style="display:flex;">
        <div style="flex:2;">课程时间</div>
        <div style="flex:8;">
    日期: <input type="date" name="lesson_date" value=""/>
    时间: <input type="time" name="lesson_time" value="08:00"/>
   课程持续时间: <input type="time" name="duration" value="01:00"/>
        </div>
    </div>
    <div style="display:flex;">
    <div style="flex:2;">录播信息</div>
    <div style="flex:8;"><textarea name="replay" style="height:6em;"></textarea></div>
    </div>

    <div style="display:flex;">
    <div style="flex:2;">语言</div>
    <div   style="flex:8;">
        <span >课程信息语言<input type="hidden" name="lang" value="" /></span>
        <span >课程语言<input type="hidden" name="speech_lang" value="" /></span>
    </div>
    </div> 



    <div style="display:flex;">
    <div style="flex:2;">标签</div>
    <div   style="flex:8;">
        <input type="input" name="tag" value="" />
    </div>
    </div> 

    <div style="display:flex;">
    <div style="flex:2;">附件</div>
    <div   style="flex:8;">
        <input type="input" name="attachment" value="" />
    </div>
    </div> 
</div>


<input type="submit" />
</form>

<script>
    name_selector_init("teacher_id",{input_id:"form_teacher"});
</script>