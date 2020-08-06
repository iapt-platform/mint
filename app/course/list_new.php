<?php
//

require_once "../path.php";
require_once "../public/_pdo.php";
require_once '../public/load_lang.php';
require_once '../media/function.php';

global $PDO;
PDO_Connect("sqlite:"._FILE_DB_COURSE_);
$query = "select * from course where 1  order by create_time DESC limit 0,4";
$Fetch = PDO_FetchAll($query);

$coverList = array();
foreach($Fetch as $row){
    $coverList[] = $row["cover"];
}
$covers = media_get($coverList);
foreach ($covers as $value) {
    $cover["{$value["id"]}"] = $value["link"];
}
foreach($Fetch as $row){
    echo '<div class="content_block">';
    echo '<div class="card">';
    
    $coverlink = $cover["{$row["cover"]}"];
    echo '<div class="v-cover">';
    if(substr($coverlink,0,6)=="media:"){
        echo '<img src="'._DIR_USER_IMG_LINK_.'/'.substr($coverlink,6).'" width="100%" height="auto">';
    }
    else{
        echo '<img src="'.$coverlink.'" width="50" height="50">';
    }
    echo '</div>';

    echo '<div class="pd-10">';
    echo '<div class="title" style="padding-bottom:5px;"><a href="../course/course.php?id='.$row["id"].'">'.$row["title"].'</a></div>';
    echo '<div class="summary"  style="padding-bottom:5px;">'.$row["subtitle"].'</div>';
    echo '<div class="summary"  style="padding-bottom:5px;">'.$row["summary"].'</div>';
    echo '<div class="author"  style="padding-bottom:5px;">主讲：'.$row["teacher"].'</div>';
    echo '</div>';
    
    echo '</div>';
    echo '</div>';
}

?>