<?php
include "./config.php";
include "./_pdo.php";

if (isset($_GET["uid"])) {
    $uid = $_GET["uid"];
} else {
    echo "no user id";
    exit;
}
if (isset($_GET["orderby"])) {
    $order_by = $_GET["orderby"];
} else {
    $order_by = "update_time";
}
if (isset($_GET["order"])) {
    $order = $_GET["order"];
} else {
    $order = "desc";
}

//获取服务器端文件列表

PDO_Connect(_FILE_DB_RESRES_INDEX_);
/*
$files = scandir($dir);
$arrlength=count($files);

for($x=0;$x<$arrlength;$x++) {
if(is_file($dir.$files[$x])){
echo $files[$x].',';
}
}
 */
switch ($order_by) {
    case "accese_time":
    case "create_time":
    case "modify_time":
        $time_show = $order_by;
        break;
    default:
        $time_show = "accese_time";
        break;
}

$query = "select * from 'index' where share='$uid' order by $order_by $order";
//echo $query."<br>";
$Fetch = PDO_FetchAll($query);
$iFetch = count($Fetch);
if ($iFetch > 0) {
    for ($i = 0; $i < $iFetch; $i++) {
        echo "<div class='file_list_shell'>";
        echo "<table style='width:100%;'><tr>";
        $filename = $Fetch[$i]["file_name"];
        $title = $Fetch[$i]["title"];
        $dir = "";
        $link = "<a href='editor.php?op=open&fileid=" . $Fetch[$i]["id"] . "&filename=$dir$filename" . "&language=sc' target='_blank'>";
        echo "<td width='70%'>$link$title</a></td>";
        echo "<td width='15%'>点击：" . $Fetch[$i]["hit"] . "</td>";
        echo "<td width='15%' style='text-align: right;'>" . date("Y-m-d h:i:sa", $Fetch[$i]["update_time"]) . "</td>";
        echo "</tr></table>";
        echo "</div>";
    }
}
