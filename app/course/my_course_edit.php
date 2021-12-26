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
    width:90%;
}
</style>

<div class="tool_bar">

<?php
require_once "../config.php";
require_once "../public/_pdo.php";
require_once '../media/function.php';

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
echo '<a href="../course/my_course_index.php?course='.$course_info["id"].'">'.$course_info["title"].'</a> > > '.$_local->gui->modify;
echo '</div>';

echo '<div></div>';
echo '</div>';


echo '<div style="display:flex;">';

echo '<div style="flex:8;padding:0 0.8em;">';
echo '<form id="course_update" action="##"   method="POST" enctype="multipart/form-data">';
echo '<input type="hidden" id="course_id" name="course" value="'.$course_info["id"].'" />';
echo '<input type="hidden" name="op" value="update" />';
echo '<div id="userfilelist">';


    echo '<div style="display:flex;">';
    echo '<div style="flex:2;">'.$_local->gui->speaker.'</div>';
    echo '<div id="teacher_id" style="flex:8;"></div>';
    echo '<input id="form_teacher" type="hidden" name="teacher" value="'.$course_info["teacher"].'" />';
    echo '</div>'; 

    echo '<div style="display:flex;">';
    echo '<div style="flex:2;">'.$_local->gui->language_select.'</div>';
    echo '<div id="teacher_id" style="flex:8;">';
    echo '<input  type="input" id="lang" name="lang" value="'.$course_info["lang"].'" />';
    echo '</div>';
    echo '</div>'; 

    echo '<div style="display:flex;">';
    echo '<div style="flex:2;">'.$_local->gui->title.'</div>';
    echo '<div style="flex:8;">';
    echo '<input type="input" id="form_title"  name = "title" value="'.$course_info["title"].'" />';
    echo '<span id = "error_form_title" ></span>';
    echo '</div>';

    echo '</div>';

    echo '<div style="display:flex;">';
    echo '<div style="flex:2;">'.$_local->gui->sub_title.'</div>';
    echo '<div style="flex:8;"><input type="input" id = "subtitle" name = "subtitle" value="'.$course_info["subtitle"].'" /></div>';
    echo '</div>';

    echo '<div style="display:flex;">';
    echo '<div style="flex:2;">'.$_local->gui->introduction.'</div>';
    echo '<div style="flex:8;"><textarea id="summary" name="summary" style="height:6em;">'.$course_info["summary"].'</textarea></div>';
    echo '</div>';

    echo '<div style="display:flex;">';
    echo '<div style="flex:2;">'.$_local->gui->tag.'</div>';
    echo '<div style="flex:8;"><input type="input" id = "tag" name = "tag" value="'.$course_info["tag"].'" /></div>';
    echo '</div>';

    echo '<div style="display:flex;">';
    echo '<div style="flex:2;">'.$_local->gui->attachment.'</div>';
    echo '<div style="flex:8;"><input type="input" id = "attachment" name = "attachment" value="'.$course_info["attachment"].'" /></div>';
    echo '</div>';

    echo '<div style="display:flex;">';
    echo '<div style="flex:2;">'.$_local->gui->status.'</div>';
    echo '<div style="flex:8;">';
    $arrStatus = array(array("id"=>0,"string"=>$_local->gui->in_recycle_bin),
                       array("id"=>20,"string"=>$_local->gui->not_started),
                       array("id"=>30,"string"=>$_local->gui->in_progress),
                       array("id"=>40,"string"=>$_local->gui->already_over));
    echo "<select id = 'status'  name = 'status'>";
    foreach ($arrStatus as $key => $value) {
        # code...

        echo "<option ";
        if((int)$course_info["status"]==$value["id"]){
            echo " selected ";
        }        
        echo " value='{$value["id"]}'>{$value["string"]}</option>";
    }
    echo "</select>";
    echo "<div class='cover'>";
    $cover_file  = _DIR_IMAGES_COURSE_."/".$course_info["id"].".jpg";
    if(!file_exists(_DIR_IMAGES_COURSE_."/".$course_info["id"].".jpg")){
            $cover_file = _DIR_IMAGES_COURSE_A_."/default.jpg";
        
    }
    else{
            $cover_file = _DIR_IMAGES_COURSE_A_."/".$course_info["id"].".jpg";
        }
    echo '<img src="'.$cover_file.'" alt="'.$_local->gui->cover.'" width="200" height="200">';
    echo "</div>";
    echo '<div><input type="file" name="cover" id="cover_file" /></div> ';
    echo '</div>';
    echo '</div>';
echo '</div>';
?>


</form>
<button  onclick="course_update()"><?php echo $_local->gui->submit ?></button>
</div>

<div style="flex:2;border-left: 1px solid var(--tool-line-color);padding-left: 12px;">
<div style="width:100%;padding:4px;">
<?php

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

<script>
    name_selector_init("teacher_id",{input_id:"form_teacher"});
</script>

</div>
		
        </div>
        
    <?php
    require_once '../studio/index_foot.php';
    ?>