<?php
//查询term字典

require_once "../path.php";
require_once "../public/_pdo.php";
require_once '../public/load_lang.php';
require_once '../public/function.php';

global $PDO;
PDO_Connect("sqlite:" . _FILE_DB_TERM_);

$query = "select word,meaning , owner from term where 1  order by create_time DESC limit 0,4";
$Fetch = PDO_FetchAll($query);

foreach ($Fetch as $row) {

    echo '<div class="card">';
    echo '<div class="title"><a href="../wiki/wiki.php?word=' . $row["word"] . '">' . $row["word"] . '</a></div>';
    echo '<div class="summary">' . $row["meaning"] . '</div>';
    echo '<div class="author">' . $row["owner"] . '</div>';

    echo '</div>';
}
