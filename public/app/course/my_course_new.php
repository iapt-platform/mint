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
require_once '../media/function.php';

echo '<div>';
echo $_local->gui->create_course;
echo '</div>';

echo '<div></div>';
echo '</div>';

?>

<form id="course_insert" action="##" method="POST">
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



</form>
<button type="submit" onclick="course_insert()"><?php echo $_local->gui->create_course ?></button>

<script>
    name_selector_init("teacher_id",{input_id:"form_teacher"});
</script>


</div>
		
        </div>
        
    <?php
    require_once '../studio/index_foot.php';
    ?>