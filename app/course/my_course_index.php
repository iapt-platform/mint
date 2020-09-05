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
    if(isset($_GET["lesson"]) || isset($_POST["lesson"])){
		if(isset($_POST["op"]) && $_POST["op"]=="update"){
			require_once("../course/my_lesson_update.php");
		}
		else{
			require_once("../course/my_lesson_edit.php");
		}
		
    }
    else if(isset($_GET["course"]) || isset($_POST["course"]) ){
		if(isset($_GET["course"])){
			$_course_id = $_GET["course"];
		}
		else if(isset($_POST["course"])){
			$_course_id = $_POST["course"];
		}
		if(isset($_GET["op"]) && $_GET["op"]=="newlesson"){
			require_once("../course/my_lesson_new.php");
		}
		else if(isset($_POST["op"]) && $_POST["op"]=="insert"){
			require_once("../course/my_lesson_insert.php");
		}
		else if(isset($_GET["op"]) && $_GET["op"]=="edit" ){
			require_once("../course/my_course_edit.php");
		}
		else if(isset($_POST["op"]) && $_POST["op"]=="update" ){
			require_once("../course/my_course_update.php");
		}
		else{
			require_once("../course/my_lesson_list.php");
		}
		
    }
    else{
		if(isset($_GET["op"]) && $_GET["op"]=="new"){
			require_once("../course/my_course_new.php");
		}
		else if(isset($_POST["op"]) && $_POST["op"]=="insert"){
			require_once("../course/my_course_insert.php");
		}
		else{
			require_once '../course/my_course_list.php';
		}
        
    }
    
    ?>
			
		</div>
		
	</div>
	
<?php
require_once '../studio/index_foot.php';
?>

