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

echo "<div  class='index_inner'>";

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

echo "<div id='lesson_list'>";


echo "</div>";

?>
</div>
<script src="../public/js/marked.js"></script>
<script>
    $("#main_video_win").height($("#main_video_win").width()*9/16);

    $.get("../course/lesson_list.php",
  {
    id:"<?php echo $_GET["id"]; ?>"
  },
  function(data,status){
      let arrLesson = JSON.parse(data);
      let html="";
    for(const lesson of  arrLesson){
        html+= '<div class="card" style="display:flex;margin:1em;padding:10px;">';

        html+= '<div style="flex:7;">';
        html+= '<div class="pd-10">';
        html+= '<div class="title" style="padding-bottom:5px;font-size:100%;font-weight:600;"><a href="../course/lesson.php?id='+lesson["id"]+'">'+lesson["title"]+'</a></div>';
        html += '<div style="overflow-y: scroll;max-height: 20em;">';
        let summary = "";
        try{
            summary = marked(lesson["summary"]);
        }
        catch{

        }
        html+= '<div class="summary"  style="padding-bottom:5px;">'+summary+'</div>';
        let live = "";
        try{
            live = marked(lesson["live"]);
        }
        catch{

        }
        html+= '<div class="summary"  style="padding-bottom:5px;">'+live+'</div>';
        let replay = "";
        try{
            replay = marked(lesson["replay"]);
        }
        catch{

        }
        html+= '<div class="summary"  style="padding-bottom:5px;">'+replay+'</div>';
        let attachment = "";
        try{
            attachment = marked(lesson["attachment"]);
        }
        catch{

        }
        html+= '<div class="summary"  style="padding-bottom:5px;">'+attachment+'</div>';
        html+= '</div>'; 
        html+= '</div>'; 
        html+= '</div>';

        html+= '<div style="flex:3;max-width:15em;">';
        let d = new Date();
        d.setTime(lesson["date"]);
        let strData = d.toLocaleDateString();
        let strTime = d.toLocaleTimeString();
         html+= '<div >开始日期：'+strData +'</div>';
         html+= '<div >开始时间：'+strTime +'</div>';
        let dt = lesson["duration"]/60;
        let sdt = "";
        if(dt>59){
            sdt += Math.floor(dt/60)+"小时";
        }
        let m = (dt % 60);
        if(m>0){
            sdt +=(dt % 60)+"分钟";
        }
        html+= "<div >持续时间："+sdt+"</div>";        
        let now = new Date(); 

        let lesson_time="";
        if(now<lesson["date"]){
            lesson_time = "尚未开始";
        }
        else if(now>lesson["date"] && now<(lesson["date"]+dt*1000)){
            lesson_time = "正在进行";
        }
        else{
            lesson_time = "已经结束";
        }
        html+= '<div ><span class="lesson_status">'+lesson_time+'</span></div>';
      
        html+= '</div>';

        html+= '</div>';

    }
    $("#lesson_list").html(html);
  });
</script>
<?php
include "../pcdl/html_foot.php";
?>