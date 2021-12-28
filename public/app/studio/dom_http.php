<?php
require_once "../config.php";
require_once "../public/_pdo.php";
require_once "../public/function.php";

if (isset($_POST["fileid"])) {
    $fileid = $_POST["fileid"];
} else {
    $fileid = "";
    echo "no file id";
    return;
}
if (isset($_POST["xmldata"])) {
    $xmldata = $_POST["xmldata"];
} else {
    $xmldata = "desc";
    echo "no file data";
    return;
}

PDO_Connect( _FILE_DB_FILEINDEX_);
$query = "select file_name from fileindex where user_id='{$_COOKIE["uid"]}' AND  id='{$fileid}'";
$Fetch = PDO_FetchOne($query);
$purefilename = $Fetch;
$FileName = _DIR_USER_DOC_ . "/" . $_COOKIE["userid"] . _DIR_MYDOCUMENT_ . "/" . $Fetch;

if ($_COOKIE["uid"]) {
    $uid = $_COOKIE["uid"];
} else {
    echo "尚未登录";
    exit;
}

//save data file
$save_filename = $FileName;
$myfile = fopen($save_filename, "w") or die("Unable to open file!");
echo $save_filename;
fwrite($myfile, $xmldata);
fclose($myfile);

$time = mTime();
$filesize = filesize($save_filename);

$query = "UPDATE fileindex SET modify_time='$time' where id=" . $PDO->quote($fileid);
$stmt = @PDO_Execute($query);
if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
    $error = PDO_ErrorInfo();
    echo "error" . $error[2] . "<br>";
} else {
    echo ("Successful");
}
