<?PHP
include "../pcdl/html_head.php";
?>
<body>

<?php
    require_once("../pcdl/head_bar.php");
?>

<style>
    #main_video_win iframe{
        width:100%;
        height:100%;
    }
</style>
<?php
//

require_once "../path.php";
require_once "../public/_pdo.php";
require_once '../media/function.php';

global $PDO;
PDO_Connect("sqlite:"._FILE_DB_COURSE_);
$query = "select * from course where id = '{$_GET["id"]}'   limit 0,1";
$Fetch = PDO_FetchAll($query);
if(count($Fetch)==0)
{
    echo "无法找到此课程。可能该课程已经被拥有者删除。";
    exit;
}
$course_info = $Fetch[0];

$coverList[] = $course_info["cover"];
$covers = media_get($coverList);
foreach ($covers as $value) {
    $cover["{$value["id"]}"] = $value["link"];
}



if(isset($_GET["lesson"])){
    $query = "select * from lesson where id = '{$_GET["lesson"]}'   limit 0,1";
    $fLesson = PDO_FetchAll($query);
    if(count($fLesson)>0){
        $lesson_info= $fLesson[0];
    }
}
echo "<div id='course_head_bar' style='background-color:gray;padding:3em 10px 10px 10px;'>";
echo $course_info["teacher"]." > ";
echo '<a href="../course/course.php?id='.$course_info["id"].'">'.$course_info["title"].'</a>';
if(isset($lesson_info)){
    echo " > ".$lesson_info["title"];
}
echo '<div class="summary"  style="padding-bottom:5px;">'.$course_info["subtitle"].'</div>';
echo '</div>';

echo '<div style="display:flex;">';
echo '<div style="flex:7;">';

    
    $coverlink = $cover["{$course_info["cover"]}"];
    echo '<div id="main_video_win" class="v-cover">';
    if(isset($lesson_info)){
        echo $lesson_info["link"];
    }
    else{
        if(substr($coverlink,0,6)=="media:"){
            echo '<img src="'._DIR_USER_IMG_ .substr($coverlink,6).'" width="100%" height="auto">';
        }
        else{
            echo '<img src="'.$coverlink.'" width="100%" height="auto">';
        }
     
    }
        echo '</div>';       
echo '</div>';
//右侧lesson列表
echo '<div style="flex:3;">';

//课程视频
$query = "select * from lesson where course_id = '{$_GET["id"]}'   limit 0,100";
$fAllLesson = PDO_FetchAll($query);

$coverList = array();
foreach($fAllLesson as $row){
    $coverList[] = $row["cover"];
}
$covers = media_get($coverList);
foreach ($covers as $value) {
    $cover["{$value["id"]}"] = $value["link"];
}

$iLesson = 1;
foreach($fAllLesson as $row){
    echo '<div class="pd-10">';
    echo 'Lesson:'.$iLesson;
    echo '<div class="title" style="padding-bottom:5px;"><a href="../course/course.php?id='.$_GET["id"].'&lesson='.$row["id"].'">'.$row["title"].'</a></div>';
    echo '<div class="author"  style="padding-bottom:5px;">主讲：'.$row["teacher"].'</div>';
    echo '</div>';
    $iLesson++;
}

echo '</div>';
echo '</div>';

echo '<div class="couse_item">';
    echo '<div class="title">';
    echo '简介';
    echo '</div>';
    echo '<div class="detail">';
        if(isset($lesson_info)){
            echo $lesson_info["summary"];
        }
        else{
            echo $course_info["summary"];
        }
    echo '</div>';
echo '</div>';

echo '<div class="couse_item">';
    echo '<div class="title">';
    echo '课程列表';
    echo '</div>';
    echo '<div class="detail">';
    foreach($fAllLesson as $row){

        echo '<div style="display:flex;">';
        echo '<div style="flex:3;">';
        $coverlink = $cover["{$row["cover"]}"];
        echo '<div class="v-cover">';
        if(substr($coverlink,0,6)=="media:"){
            echo '<img src="'._DIR_USER_IMG_LINK_.'/'.substr($coverlink,6).'" width="100%" height="auto">';
        }
        else{
            echo '<img src="'.$coverlink.'" width="50" height="50">';
        }
        echo '</div>';    
        echo '</div>';
        echo '<div style="flex:7;">';
    
        echo '<div class="pd-10">';
        echo '<div class="title" style="padding-bottom:5px;"><a href="../course/course.php?id='.$row["id"].'">'.$row["title"].'</a></div>';
        echo '<div class="summary"  style="padding-bottom:5px;">'.$row["subtitle"].'</div>';
        echo '<div class="summary"  style="padding-bottom:5px;">'.$row["summary"].'</div>';
        echo '<div class="author"  style="padding-bottom:5px;">主讲：'.$row["teacher"].'</div>';
        echo '</div>';    
    
        echo '</div>';
        echo '</div>';
    }
    echo '</div>';
echo '</div>';

?>
<script>
    $("#main_video_win").height($("#main_video_win").width()*9/16);
</script>
<?php
include "../pcdl/html_foot.php";
?>