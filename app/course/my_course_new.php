<div class="tool_bar">
<?php
require_once "../path.php";
require_once "../public/_pdo.php";
require_once '../media/function.php';

echo '<div>';
echo $_local->gui->create_course;
echo '</div>';

echo '<div></div>';
echo '</div>';

?>

<form action="../course/my_course_index.php" method="POST">
<input type="hidden" name="op" value="insert" />

<div id="userfilelist">

    <div style="display:flex;">
    <div style="flex:2;"><?php echo $_local->gui->title ?></div>
    <div style="flex:8;">
    <div style="text-align: right;color:gray;">0/32</div>
    <input type="input" name="title" value="" placeholder="<?php echo $_local->gui->title ?>" />
    </div>
    </div>

    <div style="display:none;">
    <div style="flex:2;"><?php echo $_local->gui->sub_title ?></div>
    <div style="flex:8;">
        <div style="text-align: right;color:gray;">0/32</div>
        <input type="input" name="subtitle" value="" placeholder="<?php echo $_local->gui->sub_title ?>" />
    </div>
    </div>

    <div style="display:flex;">
    <div style="flex:2;"><?php echo $_local->gui->introduction ?></div>
    <div style="flex:8;"><textarea name = "summary" style="height:6em;"></textarea></div>
    </div>

    <div style="display:flex;">
    <div style="flex:2;"><?php echo $_local->gui->speaker ?></div>
    <div   style="flex:8;">
        <div id="teacher_id"></div>
        <input id="form_teacher" type="hidden" name="teacher" value="" />
    </div>
    </div> 

    <div style="display:flex;">
    <div style="flex:2;"><?php echo $_local->gui->language_select ?></div>
    <div   style="flex:8;">
        <span >课程信息语言<input type="hidden" name="lang" value="" /></span>
    </div>
    </div> 

    <div style="display:flex;">
    <div style="flex:2;"><?php echo $_local->gui->tag ?></div>
    <div   style="flex:8;">
        <input type="input" name="tag" value="" />
    </div>
    </div> 

    <div style="display:flex;">
    <div style="flex:2;"><?php echo $_local->gui->attachment ?></div>
    <div style="flex:8;"><input type="input" name = "attachment" value="" /></div>
    </div>
</div>


<input type="submit" value="<?php echo $_local->gui->create_course ?>" />
</form>

<script>
    name_selector_init("teacher_id",{input_id:"form_teacher"});
</script>