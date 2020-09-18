<?PHP
include "../pcdl/html_head.php";
?>
<body>
<script src="../course/lesson.js"></script>
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
require_once '../ucenter/function.php';
require_once '../public/function.php';

global $PDO;
PDO_Connect("sqlite:"._FILE_DB_COURSE_);
$query = "select * from lesson where id = '{$_GET["id"]}'   limit 0,1";
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

echo "<div id='course_head_bar' style='background-color:var(--tool-bg-color1);padding:1em 10px 10px 10px;'>";
echo "<div class='index_inner '>";
echo "<div style='font-size:140%'>";
echo "<a href='../uhome/course.php?userid={$course_info["teacher"]}'>";
echo ucenter_getA($course_info["teacher"]);
echo "</a>";
echo " > ";
echo "<a href='../course/course.php?id={$course_info["id"]}'>";
echo $course_info["title"];
echo "</a>";
echo " > ";
echo $lesson_info["title"];
echo "</div>";
echo '<div class="summary"  style="padding-bottom:5px;">'.$course_info["subtitle"].'</div>';
echo '<div class="summary"  style=""><button>关注</button><button>报名</button><button>分享</button></div>';
echo "</div>";
echo '</div>';

echo "<div  class='index_inner'>";

echo "<div id='lesson_list'>";

echo "</div>";

?>
</div>
<script>

$("#main_video_win").height($("#main_video_win").width()*9/16);
lesson_show("<?php echo $_GET["id"]; ?>");

</script>
<?php
include "../pcdl/html_foot.php";
?>