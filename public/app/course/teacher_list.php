<?php
//

require_once "../config.php";
require_once "../public/_pdo.php";
require_once '../public/load_lang.php';
require_once '../ucenter/function.php';

global $PDO;
PDO_Connect( _FILE_DB_COURSE_);
$query = "SELECT teacher,count(*) as co from course where 1 group by teacher order by co DESC limit 0,4";
$Fetch = PDO_FetchAll($query);

foreach ($Fetch as $value) {
    echo '<div class="content_block">';
    echo '<div class="teacher_photo"></div>';
    echo '<div class="teacher_text">';
    echo '<div class="title"><a href="../uhome/course.php?userid=' . $value['teacher'] . '">' . ucenter_getA($value['teacher']) . '</a></div>';
    echo '<div class="teacher_intro">';
    /*
    PDO_Connect(_FILE_DB_USERINFO_);
    $query = "SELECT bio from profile where user_id = ? limit 0,10";
    $Fetch = PDO_FetchAll($query,array($value['teacher']));
    if($Fetch){
        echo $Fetch[0]["bio"];
    }
    else{
        echo "";
    }
    */
    echo '</div>';

    echo '</div>';
    echo '</div>';
}
