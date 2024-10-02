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
echo '<a href="../course/my_course_index.php?course='.$course_info["id"].'">'.$course_info["title"]."</a> > {$_local->gui->new_lesson}";
echo '</div>';

echo '<div></div>';
echo '</div>';

?>

<form  id="lesson_new" action="##" method="POST">
<input type="hidden" name="course" value="<?php echo $_GET["course"];?>" />
<input type="hidden" name="op" value="insert" />
<input type="hidden" name="course_id" value="<?php echo $_GET["course"];?>" />
<input type="hidden" name="video" value="" />

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
    <div style="flex:8;"><textarea name="summary" style="height:6em;"></textarea></div>
    </div>

    <div style="display:flex;">
    <div style="flex:2;"><?php echo $_local->gui->speaker ?></div>
    <div   style="flex:8;">
        <div id="teacher_id"><?php echo ucenter_getA($course_info["teacher"]); ?></div>
        <input id="form_teacher" type="hidden" name="teacher" value="<?php echo $course_info["teacher"] ?>" />
    </div>
    </div> 

    <div style="display:flex;">
        <div style="flex:2;"><?php echo $_local->gui->notice_live ?></div>
        <div style="flex:8;"><textarea  name="live"  style="height:6em;"></textarea></div>
    </div>

    <?php
    echo '<div style="display:flex;">';
    echo '<div style="flex:2;">'.$_local->gui->time_arrange.'</div>';
    echo '<div style="flex:8;">';
    echo "<div style='display:flex;'>";
    echo '<input type="hidden" id="form_datetime" name="form_time" value="'.time().'"/>';
    echo '<input type="hidden" id="lesson_timezone" name="lesson_timezone" value=""/>';
    echo '<div><div>'.$_local->gui->date.'</div><div id="form_date"></div></div>';
    echo '<div><div>'.$_local->gui->time.'</div><div id="form_time"></div></div>';
    echo  '<div><div>'.$_local->gui->duration.'</div><input type="time" name="duration" value="01:00"/></div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    ?>

    <div style="display:flex;">
    <div style="flex:2;"><?php echo $_local->gui->record_replay ?></div>
    <div style="flex:8;"><textarea name="replay" style="height:6em;"></textarea></div>
    </div>

    <div style="display:flex;">
    <div style="flex:2;"><?php echo $_local->gui->language_select ?></div>
    <div   style="flex:8;">
        <span >课程信息语言<input type="hidden" name="lang" value="" /></span>
        <span >课程语言<input type="hidden" name="speech_lang" value="" /></span>
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
    <div   style="flex:8;">
        <input type="input" name="attachment" value="" />
    </div>
    </div> 
</div>
</form>
<button onclick="lesson_insert()"><?php echo $_local->gui->submit ?></button>
<script>
    name_selector_init("teacher_id",{input_id:"form_teacher"});
    time_init(1);
</script>


</div>
		
        </div>
        
    <?php
    require_once '../studio/index_foot.php';
    ?>