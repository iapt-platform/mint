<?php
require_once "install_head.php";
require_once "../public/_pdo.php";
?>
<!DOCTYPE html>
<html>
<head>
</head>
<body>
<h2>Export Pali Text DB to CSV</h2>
<?php

if (isset($_GET["from"]) == false) {
    ?>
<form action="db_pali_text_export.php" method="get">
From: <input type="text" name="from" value="0"><br>
To: <input type="text" name="to" value="216"><br>
<input type="submit">
</form>
<?php
return;
}

$from = $_GET["from"];
$to = $_GET["to"];

$log = "";
echo "<h2>$from</h2>";

if ($to == 0 || $to >= 217) {
    $to = 216;
}

$book = $from + 1;
if (($fp = fopen(_DIR_PALI_TITLE_ . "/" . $book . "_title.csv", "w")) !== false) {
    fputcsv($fp, array('id', 'book', 'par_num', 'level', 'class', 'title', 'text'));
    PDO_Connect(_FILE_DB_PALITEXT_);
    $query = "SELECT id, book, paragraph, level, class, toc, text from "._TABLE_PALI_TEXT_." where book = '$book' ";
    $title_data = PDO_FetchAll($query);
    foreach ($title_data as $value) {
        $value["id"] = "NULL";
        $value["book"] = "p" . $value["book"];
        if ($value["level"] == "100") {
            $value["toc"] = "";
        }
        fputcsv($fp, $value);
    }
    fclose($fp);
    echo "ok";
} else {
    echo "error:can not open file " . _DIR_PALI_TITLE_ . "/" . $book . "_title.csv";
}

if ($from == $to) {
    echo "<h2>齐活！功德无量！all done!</h2>";
} else {
    echo "<script>";
    echo "window.location.assign(\"db_pali_text_export.php?from=" . ($from + 1) . "&to=" . $to . "\")";
    echo "</script>";
    echo "正在载入:" . ($from + 1) . "——" . $filelist[$from + 1][0];
}
?>

</body>
</html>
