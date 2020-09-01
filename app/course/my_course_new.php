<div class="tool_bar">
<?php
require_once "../path.php";
require_once "../public/_pdo.php";
require_once '../media/function.php';

echo '<div>';
echo '新建课程';
echo '</div>';

echo '<div></div>';
echo '</div>';

?>

<form action="../course/my_course_index.php" method="POST">
<input type="hidden" name="op" value="insert" />

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
    <div style="flex:8;"><textarea name = "summary" style="height:6em;"></textarea></div>
    </div>

    <div style="display:flex;">
    <div style="flex:2;">老师</div>
    <div   style="flex:8;">
        <div id="teacher_id"></div>
        <input id="form_teacher" type="hidden" name="teacher" value="" />
    </div>
    </div> 

    <div style="display:flex;">
    <div style="flex:2;">语言</div>
    <div   style="flex:8;">
        <span >课程信息语言<input type="hidden" name="lang" value="" /></span>
    </div>
    </div> 

    <div style="display:flex;">
    <div style="flex:2;">标签</div>
    <div   style="flex:8;">
        <input type="input" name="tag" value="" />
    </div>
    </div> 
</div>


<input type="submit" value="新建课程" />
</form>

<script>
    name_selector_init("teacher_id",{input_id:"form_teacher"});
</script>