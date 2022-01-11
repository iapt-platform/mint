<?php
require_once "install_head.php";
?>
<!DOCTYPE html>
<html>
<head>
</head>
<body>
<h2>Create DB</h2>
<p><a href="index.php">Home</a></p>
<?php
include "./_pdo.php";
if (isset($_GET["from"]) == false) {
    ?>
<form action="db_create.php" method="get">
From: <input type="text" name="from"><br>
To: <input type="text" name="to"><br>
Res: <input type="text" name="res"><br>
<input type="submit">
</form>
<?php
return;
}

$from = $_GET["from"];
$to = $_GET["to"];
$res = $_GET["res"];
$filelist = array();
$fileNums = 0;
$log = "";
echo "<h2>$from - $to @ $res</h2>";

if (($handle = fopen("filelist.csv", 'r')) !== false) {
    while (($filelist[$fileNums] = fgetcsv($handle, 0, ',')) !== false) {
        $fileNums++;
    }
}
if ($to == 0 || $to >= $fileNums) {
    $to = $fileNums - 1;
}

$FileName = $filelist[$from][1] . ".htm";
$fileId = $filelist[$from][0];
$fileId = $filelist[$from][0];

$dirLog = _DIR_LOG_ . "/";

$dirDb = "db/$res/";
$inputFileName = $FileName;
$outputFileNameHead = $filelist[$from][1];
$bookId = $filelist[$from][2];

$dirXmlBase = "xml/";
$dirXml = $outputFileNameHead . "/";

$xmlfile = $inputFileName;
echo "doing:" . $xmlfile . "<br>";
$log = $log . "$from,$FileName,open\r\n";

$arrInserString = array();
$db_file = $dirDb . $bookId . "_" . $res . ".db3";
PDO_Connect("$db_file");
switch ($res) {
    case "translate":
        $query = "CREATE TABLE 'data' ('id' INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL , 'paragraph' INTEGER, 'language'  TEXT, 'anchor' TEXT, 'text' TEXT, 'author' TEXT, 'editor' TEXT, 'revision' TEXT, 'edition' INTEGER, 'subver' INTEGER,'time' DATETIME DEFAULT CURRENT_TIMESTAMP)";
        break;
    case "note":
        $query = "CREATE TABLE 'data' ('id' INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL , 'paragraph' INTEGER, 'language'  TEXT, 'anchor' TEXT, 'text' TEXT, 'author' TEXT, 'editor' TEXT, 'revision' TEXT, 'edition' INTEGER, 'subver' INTEGER,'time' DATETIME DEFAULT CURRENT_TIMESTAMP)";
        break;
}

$stmt = @PDO_Execute($query);
if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
    $error = PDO_ErrorInfo();
    print_r($error[2]);
    exit;
}

$query = "CREATE INDEX 'search' ON \"data\" (\"paragraph\",\"language\",\"author\", \"editor\", \"revision\", \"edition\", \"subver\" , \"time\" )";
$stmt = @PDO_Execute($query);
if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
    $error = PDO_ErrorInfo();
    print_r($error[2]);
    $log = $log . "$from, $FileName, error, $error[2] \r\n";
}

$myLogFile = fopen($dirLog . "create_db.log", "a");
fwrite($myLogFile, $log);
fclose($myLogFile);
?>


<?php
if ($from == $to) {
    echo "<h2>齐活！功德无量！all done!</h2>";
} else {
    echo "<script>";
    echo "window.location.assign(\"db_create.php?res=" . $res . "&from=" . ($from + 1) . "&to=" . $to . "\")";
    echo "</script>";
    echo "正在载入:" . ($from + 1) . "——" . $filelist[$from + 1][0];
}
?>
</body>
</html>
