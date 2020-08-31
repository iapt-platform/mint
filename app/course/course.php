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
require_once '../ucenter/function.php';
require_once '../public/function.php';

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

echo "<div id='course_head_bar' style='background-color:var(--tool-bg-color1);padding:1em 10px 10px 10px;'>";
echo "<div class='index_inner '>";
echo "<div style='font-size:140%'>";
echo "<a href='../uhome/course.php?userid={$course_info["teacher"]}'>";
echo ucenter_getA($course_info["teacher"]);
echo "</a>";
echo " > ";
echo $course_info["title"];
echo "</div>";
echo '<div class="summary"  style="padding-bottom:5px;">'.$course_info["subtitle"].'</div>';
echo '<div class="summary"  style=""><button>关注</button><button>报名</button><button>分享</button></div>';
echo "</div>";
echo '</div>';

echo "<div class='index_inner'>";

//课程视频
$query = "select * from lesson where course_id = '{$_GET["id"]}'   limit 0,100";
$fAllLesson = PDO_FetchAll($query);
echo '<div class="card" style="margin:1em;">';
    echo '<div class="title">';
    echo '简介';
    echo '</div>';
    echo '<div class="detail">';
    echo $course_info["summary"];
    echo '</div>';
    echo '<div class="title">';
    echo '参考资料';
    echo '</div>';    
    echo '<div class="detail">';
    echo $course_info["attachment"];
    echo '</div>';   
echo '</div>';


    foreach($fAllLesson as $row){
        echo '<div class="card" style="display:flex;margin:1em;padding:10px;">';

        echo '<div style="flex:7;">';
        echo '<div class="pd-10">';
        echo '<div class="title" style="padding-bottom:5px;font-size:100%;font-weight:600;">'.$row["title"].'</div>';
        echo '<div class="summary"  style="padding-bottom:5px;">'.$row["subtitle"].'</div>';
        echo '<div class="summary"  style="padding-bottom:5px;">'.$row["summary"].'</div>';
        echo '</div>'; 
        echo '</div>';

        echo '<div style="flex:3;max-width:15em;">';
        echo '<div >开始：'.date("Y/m/d h:ia",$row["date"]/1000) .'</div>';
        $dt = $row["duration"];
        $sdt = "";
        if($dt>59){
            $sdt .= floor($dt/60)."小时";
        }
        $m = ($dt %60);
        if($m>0){
            $sdt .=($dt %60)."分钟";
        }
        echo "<div >持续：{$sdt}</div>";
        $now = mTime();
        $lesson_time="";
        if($now<$row["date"]){
            $lesson_time = "尚未开始";
        }
        else if($now>$row["date"] && $now<($row["date"]+$dt*1000)){
            $lesson_time = "已经结束";
        }
        else{
            $lesson_time = "正在进行";
        }
        echo '<div ><span class="lesson_status">已经结束</span></div>';
        echo '</div>';

        echo '</div>';
    }


?>
</div>
<script>
    $("#main_video_win").height($("#main_video_win").width()*9/16);
</script>
<?php
include "../pcdl/html_foot.php";
?>