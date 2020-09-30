<?php
//

require_once "../path.php";
require_once "../public/_pdo.php";
require_once '../public/load_lang.php';
require_once '../ucenter/function.php';

global $PDO;
PDO_Connect("sqlite:"._FILE_DB_USER_ARTICLE_);
$query = "select * from article where 1  order by create_time DESC limit 0,4";
$Fetch = PDO_FetchAll($query);

foreach($Fetch as $row){
    echo '<div class="content_block">';
    echo '<div class="card">';

    echo '<div class="pd-10">';
    echo '<div class="title" style="padding-bottom:5px;font-size:110%;font-weight:600;"><a href="../article/?id='.$row["id"].'&display=para">'.$row["title"].'</a></div>';
    echo '<div class="summary"  style="padding-bottom:5px;color: #ad4b00;">'.$row["subtitle"].'</div>';
    echo '<div class="author"  style="padding-bottom:5px;margin-bottom:0.4em;">';
    echo '<a href="../uhome/course.php?userid='.$row['owner'].'">';
    echo ucenter_getA($row["owner"]);
    echo '</a>';
    echo '</div>';    
    echo '<div class="summary"  style="padding-bottom:5px;height: 4.5em;line-height: 1.5em;overflow-y: hidden;">'.$row["summary"].'</div>';
    echo '</div>';
    echo '<div class="pd-10" style="display:flex;justify-content: space-between;">';
    echo '</div>';
    
    echo '</div>';
    echo '</div>';
}

?>