<?php
//查询term字典

require_once "../path.php";
require_once "../public/_pdo.php";
require_once '../public/load_lang.php';
require_once '../public/function.php';

global $PDO;
PDO_Connect("sqlite:"._FILE_DB_TERM_);

$query = "select word,meaning , owner from term where 1  order by create_time DESC limit 0,4";
$Fetch = PDO_FetchAll($query);

foreach($Fetch as $row){
    echo '<div class="content_block">';
    echo '<div class="card pd-10">';
    echo '<div class="title" style="padding-bottom:5px;"><a href="../wiki/wiki.php?word='.$row["word"].'">'.$row["word"].'</a></div>';
    echo '<div class="summary  style="padding-bottom:5px;">'.$row["meaning"].'</div>';
    echo '<div class="author  style="padding-bottom:5px;">'.$row["owner"].'</div>';
    echo '</div>';
    echo '</div>';
}

?>