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

    #course_frame{
        display:flex;
    }
    #course_content{
        flex:5;
        margin:1em;
        padding:10px;
    }
    #lesson_list{
        flex:5;
        height: calc(100%-100px);
        overflow-y: scroll;
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
?>

<div id='course_head_bar' style='background-color:var(--tool-bg-color1);padding:1em 10px 10px 10px;'>
<div class='index_inner '>
<div style='font-size:140%'>
<?php
echo "<a href='../uhome/course.php?userid={$course_info["teacher"]}'>";
echo ucenter_getA($course_info["teacher"]);
echo "</a>";
echo " > ";
echo $course_info["title"];
echo "</div>";
echo '<div class="summary"  style="padding-bottom:5px;">'.$course_info["subtitle"].'</div>';
echo '<div class="summary"  style="">';
echo '<button>'.$_local->gui->watch.'</button>';
echo '<button>'.$_local->gui->sign_up.'</button>';
echo '<button>'.$_local->gui->share.'</button></div>';
echo "</div>";
echo '</div>';


echo "<div id='course_frame' class='index_inner'>";

echo '<div id="course_content" style="">';
    echo '<div class="title">';
    echo $_local->gui->introduction;
    echo '</div>';
    echo '<div id="course_summary" class="detail">';
    echo '</div>';
    echo '<div class="title">';
    echo $_local->gui->attachment;
    echo '</div>';    
    echo '<div id="course_attachment"  class="detail">';
    echo '</div>';   
echo '</div>';
?>
<div id='lesson_list'>


</div>

</div>

<script>
    $("#main_video_win").height($("#main_video_win").width()*9/16);
    $.get("../course/course_get.php",
  {
    id:"<?php echo $_GET["id"]; ?>"
  },
  function(data,status){
      let arrLesson = JSON.parse(data);
      if(arrLesson && arrLesson.length>0){
        let summary = "";
        try{
            summary = marked(arrLesson[0]["summary"]);
        }
        catch(e){

        }          
        $("#course_summary").html(summary);

        let attachment = "";
        try{
            attachment = marked(arrLesson[0]["attachment"]);
        }
        catch(e){

        }          
        $("#course_attachment").html(attachment);
      }
    });

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
        html+= '<div class="title" style="padding-bottom:5px;font-size:200%;font-weight:600;"><a href="../course/lesson.php?id='+lesson["id"]+'" style="color:var(--main-color);">'+lesson["title"]+'</a></div>';
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
            //live = marked(lesson["live"]);
        }
        catch{

        }
        html+= '<div class="summary"  style="padding-bottom:5px;">'+live+'</div>';
        let replay = "";
        try{
            //replay = marked(lesson["replay"]);
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
         html+= '<div >'+gLocal.gui.date+'：'+strData +'</div>';
         html+= '<div >'+gLocal.gui.time+'：'+strTime +'</div>';
        let dt = lesson["duration"]/60;
        let sdt = "";
        if(dt>59){
            sdt += Math.floor(dt/60)+"小时";
        }
        let m = (dt % 60);
        if(m>0){
            sdt +=(dt % 60)+"分钟";
        }
        html+= "<div >"+gLocal.gui.duration+"："+sdt+"</div>";        
        let now = new Date(); 

        let lesson_time="";
        if(now<lesson["date"]){
            lesson_time = gLocal.gui.not_started;
        }
        else if(now>lesson["date"] && now<(lesson["date"]+dt*1000)){
            lesson_time = gLocal.gui.in_progress;
        }
        else{
            lesson_time = gLocal.gui.already_over;
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