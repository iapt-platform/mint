<?php
//

require_once "../path.php";
require_once "../public/_pdo.php";
require_once '../public/load_lang.php';
require_once '../media/function.php';

global $PDO;
PDO_Connect("sqlite:"._FILE_DB_COURSE_);
$query = "select teacher,count(*) as co from course where 1 group by teacher order by co DESC limit 0,4";
$Fetch = PDO_FetchAll($query);

foreach($Fetch as $value){
    echo '<div class="content_block">';
    echo '<div class="card pd-10">';
    echo '<div class="title" style="padding-bottom:5px;font-size:110%;font-weight:600;"><a href="../course/course.php?id='.$row["id"].'">'.$value['teacher'].'</a></div>';
    
    $query = "select id, title  from course where teacher = '{$value['teacher']}'  order by create_time DESC limit 0,5";
    $FetchTeacher = PDO_FetchAll($query);
    foreach($FetchTeacher as $row){
        echo '<div class="title" style="padding-bottom:5px;"><a href="../course/course.php?id='.$row["id"].'">'.$row["title"].'</a></div>';

    }    
    echo '</div>';
    echo '</div>';
}

?>