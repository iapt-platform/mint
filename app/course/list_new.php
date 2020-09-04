<?php
//

require_once "../path.php";
require_once "../public/_pdo.php";
require_once '../public/load_lang.php';
require_once '../ucenter/function.php';

global $PDO;
PDO_Connect("sqlite:"._FILE_DB_COURSE_);
$query = "select * from course where 1  order by create_time DESC limit 0,4";
$Fetch = PDO_FetchAll($query);

foreach($Fetch as $row){
    echo '<div class="content_block">';
    echo '<div class="card">';

    echo '<div class="pd-10">';
    echo '<div class="title" style="padding-bottom:5px;font-size:110%;font-weight:600;"><a href="../course/course.php?id='.$row["id"].'">'.$row["title"].'</a></div>';
    echo '<div class="summary"  style="padding-bottom:5px;color: #ad4b00;">'.$row["subtitle"].'</div>';
    echo '<div class="author"  style="padding-bottom:5px;margin-bottom:0.4em;">主讲：';
    echo '<a href="../uhome/course.php?userid='.$row['teacher'].'">';
    echo ucenter_getA($row["teacher"]);
    echo '</a>';
    echo '</div>';    
    echo '<div class="summary"  style="padding-bottom:5px;height: 4.5em;line-height: 1.5em;overflow-y: hidden;">'.$row["summary"].'</div>';
    echo '</div>';
    echo '<div class="pd-10" style="display:flex;justify-content: space-between;">';
    echo '<button>赞<span>3</span></button><button>订阅<span>23</span></button>';
    echo '</div>';
    
    echo '</div>';
    echo '</div>';
}

?>